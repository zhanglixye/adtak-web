<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Language;
use DB;

class Label extends Model
{
    /**
     * primaryKeyは、複合主キーをサポートしていないため、updateOrCreateメソッドなどを使用できない
     * 下記参照サイトURL
     * https://blog.maqe.com/solved-eloquent-doesnt-support-composite-primary-keys-62b740120f
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label_id',
        'language_id',
        'name',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    // 言語コード別のラベル一覧を返す
    public static function getLangKeySetByIds(array $label_ids)
    {
        // 言語コードをconfigで管理するパターン
        // $labels = self::whereIn('label_id', $label_ids)->get(['label_id', 'language_id', 'name']);
        // $labels = $labels->groupBy('language_id');

        // 言語コードを言語テーブルで管理するパターン
        $labels = self::select(
            'labels.label_id',
            'labels.language_id',
            'labels.name',
            'languages.code as language_code'
        )
            ->join('languages', 'labels.language_id', '=', 'languages.id')
            ->whereIn('label_id', $label_ids)
            ->get();

        $labels = $labels->groupBy('language_code');

        // データ整形
        $label_data = [];
        foreach ($labels as $key => $value) {
            $keyed = $value->mapWithKeys(function ($item) {
                 return [$item['label_id'] => $item['name']];
            });
            $label_data[$key] = $keyed->all();
        }

        return $label_data;
    }

    // 言語コード別の全てのラベル一覧を返す
    public static function getLangKeySetAll()
    {
        // 言語コードを言語テーブルで管理するパターン
        $labels = self::select(
            'labels.label_id',
            'labels.language_id',
            'labels.name',
            'languages.code as language_code'
        )
            ->join('languages', 'labels.language_id', '=', 'languages.id')
            ->get();

        $labels = $labels->groupBy('language_code');

        // データ整形
        $label_data = [];
        foreach ($labels as $key => $value) {
            $keyed = $value->mapWithKeys(function ($item) {
                return [$item['label_id'] => $item['name']];
            });
            $label_data[$key] = $keyed->all();
        }
        return $label_data;
    }
}
