<?php

namespace Maris\Geo\Service\Calculator;


use Maris\Interfaces\Geo\Calculator\DistanceCalculatorInterface;
use Maris\Interfaces\Geo\Calculator\PerpendicularDistanceCalculatorInterface;
use Maris\Interfaces\Geo\Finder\PerpendicularLocationFinderInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

/***
 * Вычисляет расстояние между линией большого круга и точкой.
 */
class PerpendicularDistanceCalculator implements PerpendicularDistanceCalculatorInterface
{

    protected DistanceCalculatorInterface $distanceCalculator;

    protected PerpendicularLocationFinderInterface $locationFinder;

    /**
     * @param DistanceCalculatorInterface $distanceCalculator
     * @param PerpendicularLocationFinderInterface $locationFinder
     */
    public function __construct(DistanceCalculatorInterface $distanceCalculator, PerpendicularLocationFinderInterface $locationFinder)
    {
        $this->distanceCalculator = $distanceCalculator;
        $this->locationFinder = $locationFinder;
    }

    /**
     * Вычисляет расстояние по перпендикуляру между линией большого
     * круга и точкой.
     * @param LocationAggregateInterface $start
     * @param LocationAggregateInterface $end
     * @param LocationAggregateInterface $point
     * @return float
     */
    public function calculatePerpendicularDistance(LocationAggregateInterface $start, LocationAggregateInterface $end, LocationAggregateInterface $point): float
    {
        return $this->distanceCalculator->calculateDistance(
            $point,
            new class( $this->locationFinder->findPerpendicularLocation( $start, $end, $point ) ) implements LocationAggregateInterface
            {
                private LocationInterface $location;

                /**
                 * @param LocationInterface $location
                 */
                public function __construct(LocationInterface $location)
                {
                    $this->location = $location;
                }

                /**
                 * @return LocationInterface
                 */
                public function getLocation(): LocationInterface
                {
                    return $this->location;
                }
            }
        );
    }
}