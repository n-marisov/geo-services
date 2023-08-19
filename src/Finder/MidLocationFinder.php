<?php

namespace Maris\Geo\Service\Finder;


use Maris\Geo\Service\Traits\LocationAggregatorConverterTrait;
use Maris\Interfaces\Geo\Factory\LocationFactoryInterface;
use Maris\Interfaces\Geo\Finder\MidLocationFinderInterface;
use Maris\Interfaces\Geo\Aggregate\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

/***
 * Вычисляет среднюю точку на линии
 */
class MidLocationFinder implements MidLocationFinderInterface
{

    use LocationAggregatorConverterTrait;

    /**
     * Фабрика для создания координат.
     * @var LocationFactoryInterface
     */
    protected LocationFactoryInterface $locationFactory;

    /**
     * @param LocationFactoryInterface $locationFactory
     */
    public function __construct(LocationFactoryInterface $locationFactory)
    {
        $this->locationFactory = $locationFactory;
    }


    /**
     * Вычисляет среднюю точку.
     * @param LocationAggregateInterface|LocationInterface $start
     * @param LocationAggregateInterface|LocationInterface $end
     * @return LocationInterface
     * @author Марисов Николай Андреевич
     */
    public function findMidLocation(LocationAggregateInterface|LocationInterface $start, LocationAggregateInterface|LocationInterface $end): LocationInterface
    {

        $start = self::deg2radLocationToArray( $start );
        $end = self::deg2radLocationToArray( $end );

        $x = cos($end["lat"]) * cos( $end["long"] - $start["long"] );
        $y = cos($end["lat"]) * sin( $end["long"] - $start["long"] );

        return $this->locationFactory->new(
            rad2deg(atan2(
                sin($start["lat"]) + sin($end["lat"]),
                sqrt( (cos($start["lat"])+$x)*(cos($start["lat"])+$x) + $y ** 2 )
            )),
            rad2deg($start["long"] + atan2($y, cos($start["lat"]) + $x))
        );
    }

}