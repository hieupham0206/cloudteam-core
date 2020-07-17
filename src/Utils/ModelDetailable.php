<?php

namespace Cloudteam\Core\Utils;

trait ModelDetailable
{
    /**
     * Lưu detail cho quan hệ 1 - n
     *
     * @param array $detailDatas
     * @param string $detailModel
     * @param $detailRelation
     * @param array $extraDatas
     *
     * @return $this
     */
    public function saveDetail($detailDatas, $detailModel, $detailRelation, $extraDatas = [])
    {
        $detailRelations       = $this->$detailRelation;
        $currentModelDetailIds = $deletedIds = $detailRelations->pluck('id');

        if ($detailDatas) {
            $deletedIds = $currentModelDetailIds->diff(collect($detailDatas)->pluck('id')->toArray());

            foreach ($detailDatas as $detailData) {
                $modelDetailid = $detailData['id'] ?? '';
                if ( ! $modelDetailid) {
                    $modelDetail = array_merge($detailData, $extraDatas);

                    $detailModel::create($modelDetail);
                } else {
                    $detailModel::query()->whereKey($modelDetailid)->update($detailData);
                }
            }
        }

        if ($deletedIds) {
            $detailModel::destroy($deletedIds);
        }

        return $this;
    }

    /**
     * Lưu detail cho quan hệ n - n
     *
     * @param array $modelIds
     * @param string $relationName
     * @param array $extraNewDatas
     */
    public function saveMany($modelIds, $relationName, $extraNewDatas = [])
    {
        $currentModelDetailIds = $deletedIds = $this->{$relationName}->pluck('id');

        if ($modelIds) {
            $deletedIds = $currentModelDetailIds->diff(collect($modelIds));

            if ($deletedIds->isNotEmpty()) {
                $this->{$relationName}()->detach($deletedIds->toArray());
            }

            $insertedIds = collect($modelIds)->diff($currentModelDetailIds);

            $this->{$relationName}()->attach($insertedIds, $extraNewDatas);
        }
    }
}
