<?php

namespace App\Services\FileManager;

/**
 * AbstractFileManagerのサブクラスを生成するクラス
 */
class FileManagerCreater
{
    /**
     * インスタンスを作成
     * @param string $path in disk
     * @param string $driver
     * @return AbstractFileManager|null null if not available
     */
    public static function createFileManagerOrNull(string $path, string $driver = 'local'): ?AbstractFileManager
    {
        $instances = [
            new ImageFileManager,
            new VideoFileManager
        ];
        for ($i = 0; $i < count($instances); $i++) {
            $ins = $instances[$i];
            $ins = $ins->disk($driver);
            if ($ins->canProcessFile($path)) {
                $ins->open($path);
                return $ins;
            }
        }
        return null;
    }
}
