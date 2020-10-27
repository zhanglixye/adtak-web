<?php

namespace App\Services\DestinationManager;

use Illuminate\Contracts\Filesystem\Filesystem;
use App\Models\DeliveryDestination;
use Throwable;

/**
 * システムがメインで使用しているストレージを操作する.
 * Operates on the system's primary storage.
 */
class LocalDestination extends AbstractDestination
{
    /** @var Filesystem $disk */
    private $disk;

    /**
     * @param DeliveryDestination $delivery_destination
     * @throws Throwable
     */
    public function __construct(DeliveryDestination $delivery_destination)
    {
        parent::__construct($delivery_destination);
        $this->disk = \Storage::disk(\Config::get('filesystems.cloud'));
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
        $this->disk->put($path, $file_content);// 強制的に上書き

        // 指定ファイルが存在するか確認
        if (!$this->disk->exists($path)) {
            throw new \Exception('Upload is failed');
        }
    }

    /**
     * @return array empty array
     */
    public static function getDestinationInfoTemplate(): array
    {
        $option = [];
        return $option;
    }
}
