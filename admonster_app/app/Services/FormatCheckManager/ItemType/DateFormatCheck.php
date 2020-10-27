<?php

namespace App\Services\FormatCheckManager\ItemType;

/**
 * 日付のフォーマットチェック
 */
class DateFormatCheck extends AbstractFormatCheck
{
    /**
     * エラーか判断
     *
     * @param string $value 値
     * @param array $setting_rules データ入力ルール
     * @return bool エラーならばtrueを返す
     */
    public static function isError(string $value, array $setting_rules): bool
    {
        if ($value !== '' && !strptime($value, '%Y/%m/%d')) {
            return true;
        }

        if ($setting_rules['selectType'] === \Config::get('const.ITEM_TYPE.DATE.ID')) {
            if (self::isErrorCheckDate($value, $setting_rules)) {
                return true;
            }
        }
        return false;
    }

    /**
     * エラーか判断
     *
     * @param string $value 値
     * @param array $setting_rules データ入力ルール
     * @return bool エラーならばtrueを返す
     */
    private static function isErrorCheckDate(string $value, array $setting_rules): bool
    {
        // 必須エラー
        if ($setting_rules['isInputRequired']) {
            if ($value === '') {
                return true;
            }
        }

        if ($setting_rules['date']['from'] !== '' && $value !== '' && strtotime($setting_rules['date']['from']) > strtotime($value)) {
            return true;
        }

        if ($setting_rules['date']['to'] !== '' && $value !== '' && strtotime($setting_rules['date']['to']) < strtotime($value)) {
            return true;
        }

        return false;
    }
}
