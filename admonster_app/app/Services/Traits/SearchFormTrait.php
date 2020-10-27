<?php

namespace App\Services\Traits;

trait SearchFormTrait
{
    /**
     * ID検索の抽出条件を追加して返却する
     *
     */
    public static function getMasterIdSearchQuery($query, $search)
    {
        $various_ids = [];
        foreach (explode(',', $search) as $key => $various_id) {
            // コード体系で階層指定の対応
            $codes = explode(config('const.MASTER_ID_PREFIX.SEPARATOR'), $various_id);
            if (count($codes) > 1) {
                foreach ($codes as $code) {
                    $prefix = substr($code, 0, 1);
                    $various_ids[config('const.MASTER_ID_PREFIX.SEPARATOR')][$key][$prefix] = substr($code, 1);
                }
                continue;
            }

            $prefix = substr($various_id, 0, 1);
            $various_ids[$prefix][] = substr($various_id, 1);
        }

        // 条件セット
        $query->where(function ($query) use ($various_ids) {
            foreach ($various_ids as $prefix => $various_id) {
                if ($prefix === config('const.MASTER_ID_PREFIX.SEPARATOR')) {
                    foreach ($various_id as $codes) {
                        $query->orWhere(function ($query) use ($codes) {
                            foreach ($codes as $prefix => $id) {
                                $column = config('const.MASTER_ID_PREFIX.TABLE_COLUMN.'. $prefix);
                                if ($column) {
                                    $query->where($column, $id);
                                }
                            }
                        });
                    }
                }
                $column = config('const.MASTER_ID_PREFIX.TABLE_COLUMN.'. $prefix);
                if ($column) {
                    $query->orWhereIn($column, $various_id);
                }
            }
        });

        return $query;
    }
}
