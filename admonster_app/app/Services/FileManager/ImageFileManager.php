<?php

namespace App\Services\FileManager;

use Image;

/**
 * 画像ファイル情報を扱うクラス
 */
class ImageFileManager extends AbstractFileManager
{
    /**
     * @var \Intervention\Image\Image
     */
    private $img;

    public function canProcessFile(string $path): bool
    {
        try {
            Image::make($this->disk->get($path));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function open(string $path): AbstractFileManager
    {
        parent::open($path);
        $this->img = Image::make($this->disk->get($path));
        return $this;
    }

    public function height(): int
    {
        return $this->img->height();
    }

    public function width(): int
    {
        return $this->img->width();
    }
}
