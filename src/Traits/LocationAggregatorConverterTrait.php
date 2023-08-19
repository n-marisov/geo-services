<?php

namespace Maris\Geo\Service\Traits;

use Maris\Interfaces\Geo\Aggregate\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

/***
 *
 */
trait LocationAggregatorConverterTrait
{
    /**
     * Приводит точка-подобный объект к точке.
     * @param LocationInterface|LocationAggregateInterface $location
     * @return LocationInterface
     */
    protected static function convertLocationAggregate( LocationInterface|LocationAggregateInterface $location ):LocationInterface
    {
        if(is_a($location,LocationAggregateInterface::class))
            return $location->getLocation();
        return $location;
    }

    /**
     * Приводит объект LocationInterface|LocationAggregateInterface
     * к массиву вида [longitude, latitude], преобразованных в радианы.
     * @param LocationInterface|LocationAggregateInterface $location
     * @return array{lat:float,long:float}
     */
    protected static function deg2radLocationToArray( LocationInterface|LocationAggregateInterface $location):array
    {
        $location = self::convertLocationAggregate( $location );
        return [
            "lat" => deg2rad( $location->getLatitude() ),
            "long" => deg2rad( $location->getLongitude() )
        ];
    }
}