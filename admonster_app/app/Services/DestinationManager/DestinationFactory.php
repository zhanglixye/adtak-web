<?php

namespace App\Services\DestinationManager;

use App\Models\DeliveryDestination;
use Throwable;

/**
 * AbstractDestination クラスの子クラスを生成する.
 * Generate child classes for the AbstractDestination class.
 */
class DestinationFactory
{
    /**
     * AbstractDestinationクラスの実装クラスのインスタンスを生成.
     * Creates an instance of an implementation class of the AbstractDestination class.
     *
     * @param DeliveryDestination $delivery_destination
     * @return AbstractDestination
     * @throws Throwable
     */
    public static function createDestination(DeliveryDestination $delivery_destination): AbstractDestination
    {
        // 必要な値があるか確認
        if (!isset($delivery_destination->type)) {
            throw new \Exception('"type" isn\'t set property');
        }
        if (!isset($delivery_destination->connection_information)) {
            throw new \Exception('"connection_information" isn\'t set property');
        }

        // create instance
        $name = \Config::get('const.DESTINATION_TYPE.' . $delivery_destination->type);
        $class = "App\Services\DestinationManager\\{$name}Destination";
        $instance = new $class($delivery_destination);

        return $instance;
    }
}
