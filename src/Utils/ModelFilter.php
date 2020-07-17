<?php

namespace Cloudteam\Core\Utils;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ModelFilter
{
    public $query;
    public $page;
    public $with;
    public $excludeIds;
    public $includeIds;
    public $orderBy;
    public $queryBy;
    public $direction;
    public $limit;
    public $offset;

    /**
     * @var Builder
     */
    public $builder;

    public $configs = [
        'query'      => '',
        'page'       => 1,
        'excludeIds' => [],
        'includeIds' => [],
        'orderBy'    => 'id',
        'queryBy'    => 'name',
        'direction'  => 'desc',
        'limit'      => 10,
        'with'       => '',
    ];

    public function __construct($builder, $options = [])
    {
        $this->builder = $builder;

        $this->configs = array_merge($this->configs, $options);

        $this->prepareConfig();

        $this->offset = ($this->page - 1) * $this->limit;
    }

    private function prepareConfig()
    {
        $request = request();

        $configs = $this->configs;
        foreach ($configs as $key => $config) {
            $this->{$key} = $request->input($key, $config);
        }
    }

    public function filter()
    {
        $this->builder->exclude($this->excludeIds)->include($this->includeIds);

        $query   = $this->query;
        $queryBy = $this->queryBy;

        if ($query) {
            $this->builder->when(Str::contains($this->queryBy, ','), static function (Builder $builder) use ($query, $queryBy) {
                $fields = explode(',', $queryBy);

                $filters = collect($fields)->mapWithKeys(static function ($key, $idx) use ($query) {
                    return [$idx => [$key, 'like', $query]];
                })->all();

                $builder->orFilterWhere($filters);

            }, static function (Builder $builder) use ($query, $queryBy) {
                $builder->where($queryBy, 'like', "%{$query}%");
            });
        }

        if ($this->with) {
            $this->builder->with(array_map('trim', explode(',', $this->with)));
        }

        return $this->builder;
    }

    public function getData($builder)
    {
        $builder->offset($this->offset)->limit($this->limit);

        if ($this->orderBy) {
            $builder->orderBy($this->orderBy, $this->direction);
        }

        return $builder->get();
    }
}
