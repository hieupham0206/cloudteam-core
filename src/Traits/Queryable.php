<?php

namespace Cloudteam\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait Queryable
{
	use Filterable;

	/**
	 * @param Builder $query
	 * @param array $conditions
	 *
	 * @return Builder
	 */
	public function scopeAndFilterWhere($query, $conditions)
	{
		if (blank($conditions)) {
			return $query;
		}

		if (is_array($conditions[0])) {
			foreach ($conditions as $condition) {
				$this->addCondition($condition);
			}

			return $this->build($query);
		}
		$this->addCondition($conditions);

		return $this->build($query);
	}

	/**
	 * @param Builder $query
	 * @param array $dates
	 * @param string $column
	 * @param string $format
	 * @param string $boolean
	 * @param bool $not
	 *
	 * @return Builder
	 */
	public function scopeDateBetween($query, $dates, $column = 'created_at', $format = 'd-m-Y', $boolean = 'and', $not = false)
	{
		[$fromDate, $toDate] = $dates;

		if (blank($fromDate) && blank($toDate)) {
			return $query;
		}

		if (blank($fromDate) && $toDate) {
			$fromDate = $toDate;
		} elseif (blank($toDate) && $fromDate) {
			$toDate = $fromDate;
		}

		$fromDate = Carbon::createFromFormat($format, $fromDate);
		$toDate   = Carbon::createFromFormat($format, $toDate);

		return $query->whereBetween($column, [
			$fromDate->toDateString() . ' 00:00:00',
			$toDate->toDateString() . ' 23:59:59',
		], $boolean, $not);
	}

	/**
	 * Query theo điều kiện giá trị field không thuộc khoảng từ start => end
	 *
	 * @param Builder $query
	 * @param array|string $excludes
	 * @param string $field : Tên field muốn query
	 *
	 * @return Builder|\Illuminate\Database\Query\Builder
	 */
	public function scopeExclude(Builder $query, $excludes, $field = 'id')
	{
		$excludes = is_array($excludes) ? $excludes : explode(',', $excludes);

		return $excludes ? $query->whereNotIn($field, $excludes) : $query;
	}

	/**
	 * @param $query
	 * @param array $filterDatas   Dữ liệu dùng để filter
	 * @param string $boolean
	 * @param array $filterConfigs Custom filter config
	 *
	 * @return mixed
	 */
	public function scopeFilters($query, $filterDatas, $boolean = 'and', array $filterConfigs = null, $insertTableName = true)
	{
		if ( ! property_exists($this, 'filters') || blank($filterDatas)) {
			return $query;
		}

		//property $filters của model
		$filters = $this->filters;
		if ($filterConfigs) {
			$filters = array_merge($filters, $filterConfigs);
		}

		foreach ($filters as $column => $operator) {
			if (isset($filterDatas[$column]) || is_array($operator)) {
				$filterVal = $filterDatas[$column] ?? '';
				$this->addCondition([$column, $operator, $filterVal], $boolean, $filterDatas, $insertTableName);
			}
		}

		return $this->build($query);
	}

	/**
	 * Query theo điều kiện giá trị field thuộc khoảng từ start => end
	 *
	 * @param Builder $query
	 * @param array|string $includes
	 * @param string $field : Tên field muốn query
	 *
	 * @return Builder|\Illuminate\Database\Query\Builder
	 */
	public function scopeInclude(Builder $query, $includes, $field = 'id')
	{
		$includes = is_array($includes) ? $includes : explode(',', $includes);

		return $includes ? $query->whereIn($field, $includes) : $query;
	}

	/**
	 * @param Builder $query
	 * @param array $conditions
	 *
	 * @return Builder
	 */
	public function scopeOrFilterWhere($query, $conditions)
	{
		if (blank($conditions)) {
			return $query;
		}

		if (is_array($conditions[0])) {
			foreach ($conditions as $condition) {
				$this->addCondition($condition, 'or');
			}

			return $this->build($query);
		}
		$this->addCondition($conditions, 'or');

		return $this->build($query);
	}

	public function scopeLimitOffset($query, $limit, $offset)
	{
		return $query->limit($limit)->offset($offset);
	}

	public function scopeWithWhereHas($query, $relation, $constraint)
	{
		return $query->whereHas($relation, $constraint)->with([$relation => $constraint]);
	}
}
