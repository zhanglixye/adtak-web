<?php

namespace App\Services\FormatCheckManager\ItemType;

/**
 * フォーマットチェックをする抽象クラス
 */
abstract class AbstractFormatCheck
{
    /**
     * エラーか判断
     *
     * @param string $value 値
     * @param array $setting_rules データ入力ルール
     * @return bool
     */
    abstract public static function isError(string $value, array $setting_rules): bool;
}
