<?php

namespace Maris\Geo\Service\Calculator;


use Maris\Interfaces\Geo\Calculator\DistanceCalculatorInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;

/***
 * Калькулятор Хаверсайна.
 * @author Марисов Николай Андреевич.
 */
class Haversine implements DistanceCalculatorInterface
{

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
     * @param LocationAggregateInterface $start
     * @param LocationAggregateInterface $end
     * @return float
     */
    public function calculateDistance(LocationAggregateInterface $start, LocationAggregateInterface $end): float
    {
        $start = $start->getLocation();
        $end = $end->getLocation();
        $lat1 = deg2rad( $start->getLatitude() );
        $lng1 = deg2rad( $start->getLongitude() );
        $lat2 = deg2rad( $end->getLatitude() );
        $lng2 = deg2rad( $end->getLongitude() );
        return $this->earthRadius * 2 * asin(
                sqrt(
                    (sin(($lat2 - $lat1) / 2) ** 2) +
                    cos($lat1) * cos($lat2) * (sin(($lng2 - $lng1) / 2) ** 2)
                )
            );
    }
}