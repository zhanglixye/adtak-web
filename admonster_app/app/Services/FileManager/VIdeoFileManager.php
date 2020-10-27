<?php

namespace App\Services\FileManager;

use FFMpeg;

/**
 * 動画ファイル情報を扱うクラス
 */
class VideoFileManager extends AbstractFileManager
{
    /**
     * @var FFMpeg\FFProbe\DataMapping\Stream
     */
    private $mediaStream;

    public function canProcessFile(string $path): bool
    {
        try {
            FFMpeg::fromFilesystem($this->disk)->open($path);
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public function open(string $path): AbstractFileManager
    {
        parent::open($path);
        $media = FFMpeg::fromDisk($this->disk_name)->open($path);
        $this->mediaStream = $media->getFirstStream();
        return $this;
    }

    public function height(): int
    {
        return $this->mediaStream->get('height');
    }

    public function width(): int
    {
        return $this->mediaStream->get('width');
    }
}
