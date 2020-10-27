<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ItemConfig extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'step_id',
        'sort',
        'label_id',
        'item_key',
        'item_type',
        'option',
        'is_required',
        'diff_check_level',
        'is_deleted',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * 指定作業IDの作業項目設定リストを取得する
     *
     * @param int $step_id 作業ID
     * @param bool $isValue 選択項目有無
     * @return array
     */
    public static function getRequestContentsItemList($step_id, $isValue = false)
    {
        // 作業項目リストを取得
        $item_configs = self::select(
            'id',
            'item_key',
            'item_type',
            'label_id',
            'option'
        )
            ->where('step_id', $step_id)
            ->where('item_key', 'not like', 'results%') //結果項目除去
            ->whereNotNull('label_id') //ラベル定義済
            ->WhereNotNull('item_type')
            ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->orderBy('sort')
            ->get();

        $items = [];
        foreach ($item_configs as $item_config) {
            $item_keys = explode('/', $item_config->item_key);
            $item_config->group = reset($item_keys);
            $item_config->key = end($item_keys);
            if ($item_config->group == $item_config->key) {
                continue;
            }

            $item_config->option = $item_config->option ? json_decode($item_config->option) : new \stdClass();
            if ($isValue) {
                $item_config->item_config_values = $item_config->itemConfigValues()
                    ->select('label_id', 'sort')
                    ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
                    ->orderBy('sort')
                    ->get();
            }
            $items[$item_config->group][] = $item_config;
        }
        return $items;
    }

    public function itemConfigValues()
    {
        return $this->hasMany(ItemConfigValue::class);
    }
}
