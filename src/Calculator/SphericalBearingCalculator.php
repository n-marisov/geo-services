<?php

namespace Maris\Geo\Service\Calculator;


use Maris\Geo\Service\Traits\LocationAggregatorConverterTrait;
use Maris\Interfaces\Geo\Calculator\BearingCalculatorInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

/**
 * Калькулятор азимутов сферической земли.
 * @author Марисов Николай Андреевич.
 */
class SphericalBearingCalculator implements BearingCalculatorInterface
{
    use LocationAggregatorConverterTrait;
    /**
     * @inheritDoc
     */
    public function calculateInitialBearing(LocationAggregateInterface|LocationInterface $start, LocationAggregateInterface|LocationInterface $end): float
    {
        $start = self::deg2radLocationToArray( $start );
        $end = self::deg2radLocationToArray( $end );

        $bearing = rad2deg(
            atan2(
                sin($end["long"] - $start["long"]) * cos($end["lat"]),
                cos($start["lat"]) * sin($end["lat"]) - sin($start["lat"]) * cos($end["lat"]) * cos($end["long"] - $start["long"])
            )
        );

        if ($bearing < 0)
            $bearing = fmod($bearing + 360, 360);

        return $bearing;
    }

    /**
     * @inheritDoc
     */
    public function calculateFinalBearing(LocationAggregateInterface|LocationInterface $start, LocationAggregateInterface|LocationInterface $end): float
    {
        return fmod($this->calculateInitialBearing( $end, $start ) + 180, 360);
    }
}