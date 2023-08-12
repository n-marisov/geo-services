<?php

namespace Maris\Geo\Service\Calculator;


use Maris\Interfaces\Geo\Calculator\BearingCalculatorInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;

/**
 * Калькулятор азимутов сферической земли.
 * @author Марисов Николай Андреевич.
 */
class SphericalBearingCalculator implements BearingCalculatorInterface
{

    /**
     * @inheritDoc
     */
    public function calculateInitialBearing(LocationAggregateInterface $start, LocationAggregateInterface $end): float
    {
        $start = $start->getLocation();
        $end = $end->getLocation();
        $lat1 = deg2rad( $start->getLatitude() );
        $lng1 = deg2rad( $start->getLongitude() );
        $lat2 = deg2rad( $end->getLatitude() );
        $lng2 = deg2rad( $end->getLongitude() );

        $y = sin($lng2 - $lng1) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($lng2 - $lng1);

        $bearing = rad2deg(atan2($y, $x));

        if ($bearing < 0)
            $bearing = fmod($bearing + 360, 360);

        return $bearing;
    }

    /**
     * @inheritDoc
     */
    public function calculateFinalBearing(LocationAggregateInterface $start, LocationAggregateInterface $end): float
    {
        return fmod($this->calculateInitialBearing( $end, $start ) + 180, 360);
    }
}