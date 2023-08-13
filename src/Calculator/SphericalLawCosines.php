<?php

namespace Maris\Geo\Service\Calculator;


use Maris\Geo\Service\Traits\LocationAggregatorConverterTrait;
use Maris\Interfaces\Geo\Calculator\DistanceCalculatorInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

/***
 * Калькулятор для расчета расстояния
 * использующий сферический закон косинусов.
 */
class SphericalLawCosines implements DistanceCalculatorInterface
{
    use LocationAggregatorConverterTrait;
    /***
     * Радиус земного шара для расчетов.
     * @var float
     */
    protected readonly float $earthRadius;

    /**
     * @inheritDoc
     */
    public function calculateDistance(LocationAggregateInterface|LocationInterface $start, LocationAggregateInterface|LocationInterface $end): float
    {
        $start = self::deg2radLocationToArray( $start );
        $end = self::deg2radLocationToArray( $end );

        return $this->earthRadius *  acos(
            sin($start["lat"]) * sin($end["lat"]) +
            cos($start["lat"]) * cos($end["lat"]) * cos( $end["lon"] - $start["lon"])
            );
    }
}