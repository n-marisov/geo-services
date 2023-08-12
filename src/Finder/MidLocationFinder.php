<?php

namespace Maris\Geo\Service\Finder;


use Maris\Interfaces\Geo\Factory\LocationFactoryInterface;
use Maris\Interfaces\Geo\Finder\MidLocationFinderInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

/***
 * Вычисляет среднюю точку на линии
 */
class MidLocationFinder implements MidLocationFinderInterface
{
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
     * @param LocationAggregateInterface $start
     * @param LocationAggregateInterface $end
     * @return LocationInterface
     * @author Марисов Николай Андреевич
     */
    public function findMidLocation(LocationAggregateInterface $start, LocationAggregateInterface $end): LocationInterface
    {
        $start = $start->getLocation();
        $end = $end->getLocation();
        $lat1 = deg2rad( $start->getLatitude() );
        $lng1 = deg2rad( $start->getLongitude() );
        $lat2 = deg2rad( $end->getLatitude() );
        $lng2 = deg2rad( $end->getLongitude() );


        $x = cos($lat2) * cos( $lng2 - $lng1 );
        $y = cos($lat2) * sin( $lng2 - $lng1 );

        return $this->locationFactory->new(
            rad2deg(atan2(
                sin($lat1) + sin($lat2),
                sqrt( (cos($lat1)+$x)*(cos($lat1)+$x) + $y ** 2 )
            )),
            rad2deg($lng1 + atan2($y, cos($lat1) + $x))
        );
    }

}