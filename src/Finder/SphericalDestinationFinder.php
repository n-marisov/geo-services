<?php

namespace Maris\Geo\Service\Finder;

use Maris\Geo\Service\Traits\LocationAggregatorConverterTrait;
use Maris\Interfaces\Geo\Factory\LocationFactoryInterface;
use Maris\Interfaces\Geo\Finder\DestinationFinderInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

class SphericalDestinationFinder implements DestinationFinderInterface
{
    use LocationAggregatorConverterTrait;
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
     * @param LocationAggregateInterface|LocationInterface $location
     * @param float $initialBearing
     * @param float $distance
     * @return LocationInterface
     */
    public function findDestination(LocationAggregateInterface|LocationInterface $location, float $initialBearing, float $distance): LocationInterface
    {
        $l = self::deg2radLocationToArray( $location );
        $d = $distance / $this->earthRadius;
        $b = deg2rad( $initialBearing );

        return $this->locationFactory->new(
            rad2deg( asin(sin($l["lat"]) * cos($d) + cos($l["lat"]) * sin($d) * cos($b)) ),
            rad2deg($l["long"] + atan2(sin($b) * sin($d) * cos($l["lat"]), cos($d) - sin($l["lat"]) * sin($l["lat"])) )
        );
    }
}