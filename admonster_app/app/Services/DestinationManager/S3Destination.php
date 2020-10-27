<?php

namespace App\Services\DestinationManager;

use Illuminate\Contracts\Filesystem\Cloud;
use App\Models\DeliveryDestination;
use Throwable;

/**
 * AWSのS3を操作するクラス.
 * Work with AWS S3.
 */
class S3Destination extends AbstractDestination
{
    /** @var Cloud $disk */
    private $disk;

    /**
     * create Cloud class
     *
     * @param DeliveryDestination $delivery_destination Delivery destination held in class
     * @throws Throwable
     */
    public function __construct(DeliveryDestination $delivery_destination)
    {
        parent::__construct($delivery_destination);
        // S3を使用するか判定
        $this->disk = \Storage::createS3Driver(json_decode($delivery_destination->connection_information, true));
    }

    /**
     * Force file overwrite
     *
     * @param string $file_path example:fizz/buzz.txt
     * @param mixed $file_content file content
     * @throws Throwable
     */
    public function putFile(string $file_path, $file_content): void
    {
        $path = $this->delivery_destination->path . '/' . $file_path;
        $this->disk->put($path, $file_content);// 上書き

        // 指定ファイルが存在するか確認
        if (!$this->disk->exists($path)) {
            throw new \Exception('Upload is failed');
        }
    }

    /**
     * @return array{key:string,secret:string,region:string,bucket:string} Refer to filesystems.php
     * @link https://laravel.com/docs/5.7/filesystem#downloading-files
     */
    public static function getDestinationInfoTemplate(): array
    {
        $option = [
            'key' => '',
            'secret' => '',
            'region' => '',
            'bucket' => '',
        ];
        return $option;
    }
}
