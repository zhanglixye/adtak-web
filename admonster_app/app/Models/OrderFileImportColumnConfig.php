<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFileImportColumnConfig extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_file_import_main_config_id',
        'column',
        'item',
        'label_id',
        'item_type',
        'rule',
        'display_format',
        'is_output',
        'sort',
        'subject_part_no',
        'created_user_id',
        'updated_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * 属性に対するモデルのデフォルト値
     *
     * @var array
     */
    protected $attributes = [
        'is_output' => true,
    ];

    /**
     * 該当の表示形式を取得
     * @param int $display_format 表示形式
     * @param int $item_type_id 項目のデータタイプID
     * @return int[] $display_formats
     */
    public static function getTargetDisplayFormat(int $display_format, int $item_type_id)
    {
        // 項目名のタイプ名を取得
        $item_types = \Config::get('const.ITEM_TYPE');
        /** @var string|null */
        $item_type_key = null;
        foreach ($item_types as $key => $item_type) {
            if ($item_type['ID'] === $item_type_id) {
                $item_type_key = $key;
                break;
            }
        }

        // 指定された表示形式を取得
        $item_type_display_formats = \Config::get('const.DISPLAY_FORMAT');
        arsort($item_type_display_formats);
        $display_formats = [];
        foreach ($item_type_display_formats as $key => $item_type_display_format) {
            $end = mb_strpos($key, '_');
            if (!in_array(mb_substr($key, 0, $end), [$item_type_key])) {
                continue;
            }

            if (bindec(decbin($display_format) & decbin($item_type_display_format)) === $item_type_display_format) {
                array_push($display_formats, $item_type_display_format);
            }
        }
        return $display_formats;
    }

    /* -------------------- relations ------------------------- */

    public function orderFileImportMainConfig()
    {
        return $this->belongsTo(OrderFileImportMainConfig::class);
    }

    public function orderDetailValues()
    {
        return $this->belongsToMany(OrderDetailValue::class, 'order_file_import_column_configs_order_detail_values');
    }
}
