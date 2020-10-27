<?php

namespace App\Services\FileManager;

/**
 * ファイル情報を扱う抽象クラス
 */
abstract class AbstractFileManager
{
    /**
     * disk
     */
    protected $disk = null;

    /**
     * @var string
     */
    protected $disk_name = '';

    /**
     * ファイルシステムをセット
     * @param string $name
     * @return AbstractFileManager
     */
    public function disk(string $name): AbstractFileManager
    {
        $this->disk_name = $name;
        $this->disk = \Storage::disk($name);
        return $this;
    }

    /**
     * ファイルを開く
     * @param string $path
     * @return AbstractFileManager
     * @throws \Throwable diskを設定していなかったり、指定したファイルがない場合
     */
    public function open(string $path): AbstractFileManager
    {
        if (is_null($this->disk)) {
            throw new \Exception('Driver not set');
        }
        if (!$this->disk->exists($path)) {
            throw new \Exception('File does not exist');
        }
        return $this;
    }

    /**
     * 処理可能なファイルか判定する
     * @param string $path 指定したストレージ内のパス
     * @return bool
     */
    abstract public function canProcessFile(string $path): bool;

    /**
     * 高さを取得
     * @return int
     */
    abstract public function height(): int;

    /**
     * 横幅を取得
     * @return int
     */
    abstract public function width(): int;
}
