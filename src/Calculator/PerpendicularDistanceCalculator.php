<?php

namespace Maris\Geo\Service\Calculator;


use Maris\Interfaces\Geo\Calculator\DistanceCalculatorInterface;
use Maris\Interfaces\Geo\Calculator\PerpendicularDistanceCalculatorInterface;
use Maris\Interfaces\Geo\Finder\PerpendicularLocationFinderInterface;
use Maris\Interfaces\Geo\Aggregate\LocationAggregateInterface;
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
     * @param LocationAggregateInterface|LocationInterface $start
     * @param LocationAggregateInterface|LocationInterface $end
     * @param LocationAggregateInterface|LocationInterface $point
     * @return float
     */
    public function calculatePerpendicularDistance(LocationAggregateInterface|LocationInterface $start, LocationAggregateInterface|LocationInterface $end, LocationAggregateInterface|LocationInterface $point): float
    {
        return $this->distanceCalculator->calculateDistance(
            $point,
            $this->locationFinder->findPerpendicularLocation( $start, $end, $point )
        );
    }
}