<?php

namespace App\Services\FormatCheckManager\ItemType;

/**
 * 数値のフォーマットチェック
 */
class NumFormatCheck extends AbstractFormatCheck
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
        if ($value !== '' && !preg_match('/^(-[0-9]+|0)$|^([0-9]+)$/', $value)) {
            return true;
        }

        // システム上限エラー
        if ($value !== '' && 999999999999 < intval($value)) {
            return true;
        }

        // システム下限エラー
        if ($value !== '' && -999999999999 > intval($value)) {
            return true;
        }

        if ($setting_rules['selectType'] === \Config::get('const.ITEM_TYPE.NUM.ID')) {
            if (self::isErrorCheckNum($value, $setting_rules)) {
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
    private static function isErrorCheckNum(string $value, array $setting_rules): bool
    {
        // 必須エラー
        if ($setting_rules['isInputRequired']) {
            if ($value === '') {
                return true;
            }
        }

        // 上限エラー
        if ($setting_rules['size']['upperLimit'] !== '' && $value !== '' && $setting_rules['size']['upperLimit'] < intval($value)) {
            return true;
        }

        // 下限エラー
        if ($setting_rules['size']['lowerLimit'] !== '' && $value !== '' && intval($setting_rules['size']['lowerLimit']) > intval($value)) {
            return true;
        }

        return false;
    }
}
