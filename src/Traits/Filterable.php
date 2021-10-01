<?php

namespace Cloudteam\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
	private $conditions = [];

	private $foreignRelation = false;

	/**
	 * @param        $configs
	 * @param string $boolean
	 * @param array  $filterDatas
	 *
	 * @return void
	 */
	public function addCondition($configs, $boolean = 'and', $filterDatas = [], $insertTableName = true): void
	{
		[$column, $operator, $value] = $configs;

		if (! blank($value) || is_array($operator)) {
			[$column, $value] = $this->preparedParam($operator, $column, $value, $filterDatas, $insertTableName);

			if ($value) {
				$this->conditions[] = [$column, $value, $boolean, $operator];
			}
		}
	}

	/**
	 * @param Builder $query
	 *
	 * @return mixed
	 */
	private function build(Builder $query)
	{
		return $query->where(function (Builder $subQuery) {
			foreach ($this->conditions as $condition) {
				[$column, $value, $boolean, $operator] = $condition;

				if ($relation = $this->foreignRelation) {
					$self = $this;
					$subQuery->whereHas($relation, static function (Builder $q) use ($column, $value, $operator, $boolean, $self) {
						$self->addQueryCondition($q, $value, $operator, $column, $boolean);
					});
				} else {
					$this->addQueryCondition($subQuery, $value, $operator, $column, $boolean);
				}
			}

			return $subQuery;
		});
	}

	private function addQueryCondition(&$subQuery, $value, $operator, $column, $boolean)
	{
		if (is_array($value) && $value) {
			if (is_array($operator)) {
				$subQuery->dateBetween($value, $column);
			} else {
				$subQuery->whereIn($column, $value, $boolean, $operator === '!=');
			}
		} else {
			if ($this->foreignRelation) {
				$columns = explode('.', $column);
				$subQuery->where(end($columns), $operator, $value, $boolean);
			} else {
				$subQuery->where($column, $operator, $value, $boolean);
			}
		}
	}

	/**
	 * @param       $operator
	 * @param       $column
	 * @param       $value
	 * @param array $filterDatas
	 *
	 * @return array
	 */
	private function preparedParam($operator, $column, $value, $filterDatas = [], $insertTableName = true): array
	{
		$tableName = $this->getTable();

		if (strpos($column, '.') !== false) {
			$columns = explode('.', $column);
			$column  = array_pop($columns);

			$relation              = implode('.', $columns);
			$column                = "$relation.$column";
			$this->foreignRelation = $relation;
		} elseif ($insertTableName) {
			$column = "$tableName.$column";
		}

		if (is_array($operator)) {
			//note: filter dateBetween
			$operatorType = $operator['type'];
			$params       = $operator['params'];

			if ($operatorType === 'date_between') {
				$values = [];
				foreach ($params as $param) {
					if (isset($filterDatas[$param])) {
						$values[] = $filterDatas[$param];
					}
				}

				$value = $values;
			}
		} elseif (is_string($operator) && strtolower($operator) === 'like') {
			$value = "%$value%";
		}

		return [$column, $value];
	}
}
