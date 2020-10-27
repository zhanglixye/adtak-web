<?php

namespace App\Services\DestinationManager;

use App\Models\DeliveryDestination;

/**
 * ファイル操作の抽象クラス.
 * abstract classes for working with files.
 */
abstract class AbstractDestination
{
    /** @var DeliveryDestination delivery destination*/
    protected $delivery_destination;

    /**
     * Keep the delivery destination class on itself
     *
     * @param DeliveryDestination $delivery_destination destination held in class
     */
    public function __construct(DeliveryDestination $delivery_destination)
    {
        $this->delivery_destination = $delivery_destination;
    }

    /**
     * Force file overwrite
     *
     * @param string $file_path example:fizz/buzz.txt
     * @param mixed $file_content file content
     */
    abstract public function putFile(string $file_path, $file_content): void;

    /**
     * @return array connection information(associative array)
     */
    abstract public static function getDestinationInfoTemplate(): array;

    /**
     * get "type" in this instance
     *
     * @return int CONST.DESTINATION_TYPE
     */
    public function getType(): int
    {
        return $this->delivery_destination->type;
    }

    /**
     * get "path" in this instance
     *
     * @return string path
     */
    public function getPath(): string
    {
        return $this->delivery_destination->path;
    }
}
