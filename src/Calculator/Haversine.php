<?php

namespace Maris\Geo\Service\Calculator;


use Maris\Geo\Service\Traits\LocationAggregatorConverterTrait;
use Maris\Interfaces\Geo\Aggregate\LocationAggregateInterface;
use Maris\Interfaces\Geo\Calculator\DistanceCalculatorInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

/***
 * Калькулятор Хаверсайна.
 * @author Марисов Николай Андреевич.
 */
class Haversine implements DistanceCalculatorInterface
{
    use LocationAggregatorConverterTrait;

    /***
     * Радиус земного шара для расчетов.
     * @var float
     */
    protected readonly float $earthRadius;

    /**
     * @param float $earthRadius
     */
    public function __construct( float $earthRadius )
    {
        $this->earthRadius = $earthRadius;
    }

    /**
     * Вычисляет расстояния между двумя точками.
     * @param LocationAggregateInterface|LocationInterface $start
     * @param LocationAggregateInterface|LocationInterface $end
     * @return float
     */
    public function calculateDistance(LocationAggregateInterface|LocationInterface $start, LocationAggregateInterface|LocationInterface $end): float
    {
        $start = self::deg2radLocationToArray( $start );
        $end = self::deg2radLocationToArray( $end );
        return $this->earthRadius * 2 * asin(
                sqrt(
                    (sin(($end["lat"] - $start["lat"]) / 2) ** 2) +
                    cos($start["lat"]) * cos($end["lat"]) * (sin(($end["long"] - $start["long"]) / 2) ** 2)
                )
            );
    }
}