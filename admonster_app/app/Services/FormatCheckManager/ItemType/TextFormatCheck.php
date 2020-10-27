<?php

namespace App\Services\FormatCheckManager\ItemType;

/**
 * テキストのフォーマットチェック
 */
class TextFormatCheck extends AbstractFormatCheck
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
        // システム文字数制限エラー
        if (mb_strlen($value) > 2000) {
            return true;
        }

        if ($setting_rules['selectType'] === \Config::get('const.ITEM_TYPE.STRING.ID')) {
            if (self::isErrorCheckText($value, $setting_rules)) {
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
    private static function isErrorCheckText(string $value, array $setting_rules): bool
    {
        // 文字数制限エラー
        if ($setting_rules['fontSize'] !== '') {
            if (mb_strlen($value) > $setting_rules['fontSize']) {
                return true;
            }
        }

        // 必須エラー
        if ($setting_rules['isInputRequired']) {
            if ($value === '') {
                return true;
            }
        }

        if ($setting_rules['isUseInputLimit'] == \Config::get('const.FLG.ACTIVE') && count($setting_rules['selectedCheckBoxItems'])) {
            $regexp = '/^[\n';
            foreach ($setting_rules['selectedCheckBoxItems'] as $selectedCheckBoxItem) {
                if ($selectedCheckBoxItem === \Config::get('const.INPUT_RULE.TEXT.HALFWIDTH_ALPHANUMERIC_SYMBOL')) {
                    $regexp .= 'a-zA-Z0-9!-~ ';
                } elseif ($selectedCheckBoxItem === \Config::get('const.INPUT_RULE.TEXT.FULLWIDTH_ALPHANUMERIC_SYMBOL')) {
                    $regexp .= 'ａ-ｚＡ-Ｚ０-９　！゛“”＃＄％＆‘’（）＊＋，、ー．。・／｜：；＜＝＞？＠￥＼（）〔〕［］｛｝〈〉《》「」『』【】＾＿‘｛｜｝～';
                } elseif ($selectedCheckBoxItem === \Config::get('const.INPUT_RULE.TEXT.FULLWIDTH_HIRAGANA_KATAKANA')) {
                    $regexp .= 'ぁ-んァ-ヶー　';
                }
            }
            $regexp .= ']+$/u';
            if ($value !== '' && !preg_match($regexp, $value)) {
                return true;
            }
        }

        return false;
    }
}
