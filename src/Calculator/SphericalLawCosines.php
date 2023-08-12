<?php

namespace Maris\Geo\Service\Calculator;


use Maris\Interfaces\Geo\Calculator\DistanceCalculatorInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;

/***
 * Калькулятор для расчета расстояния
 * использующий сферический закон косинусов.
 */
class SphericalLawCosines implements DistanceCalculatorInterface
{

    /***
     * Радиус земного шара для расчетов.
     * @var float
     */
    protected readonly float $earthRadius;

    /**
     * @inheritDoc
     */
    public function calculateDistance(LocationAggregateInterface $start, LocationAggregateInterface $end): float
    {
        $start = $start->getLocation();
        $end = $end->getLocation();

        $lat1 = deg2rad( $start->getLatitude() );
        $lng1 = deg2rad( $start->getLongitude() );
        $lat2 = deg2rad( $end->getLatitude() );
        $lng2 = deg2rad( $end->getLongitude() );

        return $this->earthRadius *  acos( sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos( $lng2 - $lng1) );
    }
}