<?php

namespace App\Services\FileManager;

/**
 * ファイル名を管理するクラス
 */
class FileNameManager
{
    /**
     * Windowsのフォルダ・ファイル名として使えない文字列を削除
     * @param string|string[] $subject 検索・置換の対象となる文字列もしくは、配列
     * @return mixed 特定の文字列を削除した文字列・配列
     */
    public static function removeSpecialCharacterForWindows($subject)
    {
        // Windowsのフォルダ・ファイルに使えない文字の配列
        $search = ['\\', '/', ':', '*', '?' , '"', '<', '>', '|'];
        $replace = '';
        return str_replace($search, $replace, $subject);
    }
}
