<?php

namespace Maris\Geo\Service\Finder;

use Maris\Interfaces\Geo\Factory\LocationFactoryInterface;
use Maris\Interfaces\Geo\Finder\DestinationFinderInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

class SphericalDestinationFinder implements DestinationFinderInterface
{

    /***
     * Фабрика для создания координат.
     * @var LocationFactoryInterface
     */
    protected LocationFactoryInterface $locationFactory;

    /***
     * Радиус земного шара для расчетов.
     * @var float
     */
    protected readonly float $earthRadius;

    /**
     * @param LocationFactoryInterface $locationFactory
     * @param float $earthRadius
     */
    public function __construct(LocationFactoryInterface $locationFactory, float $earthRadius)
    {
        $this->locationFactory = $locationFactory;
        $this->earthRadius = $earthRadius;
    }


    /**
     * @param LocationAggregateInterface $location
     * @param float $initialBearing
     * @param float $distance
     * @return LocationInterface
     */
    public function findDestination(LocationAggregateInterface $location, float $initialBearing, float $distance): LocationInterface
    {
        $location = $location->getLocation();

        $d = $distance / $this->earthRadius;
        $b = deg2rad( $initialBearing );
        $y = deg2rad( $location->getLatitude() );
        $x = deg2rad( $location->getLongitude() );

        return $this->locationFactory->new(
            rad2deg( asin(sin($y) * cos($d) + cos($y) * sin($d) * cos($b)) ),
            rad2deg($x + atan2(sin($b) * sin($d) * cos($y), cos($d) - sin($y) * sin($y)) )
        );
    }
}