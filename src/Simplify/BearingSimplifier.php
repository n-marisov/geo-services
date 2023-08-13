<?php

namespace Maris\Geo\Service\Simplify;


use Maris\Interfaces\Geo\Calculator\BearingCalculatorInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;
use Maris\Interfaces\Geo\Simplifier\SimplifierInterface;

/***
 * @author Марисов Николай Андреевич.
 */
class BearingSimplifier implements SimplifierInterface
{
    /**
     * @var BearingCalculatorInterface
     */
    protected BearingCalculatorInterface $bearingCalculator;

    /**
     * @var float
     */
    protected float $bearing;

    /**
     * @param BearingCalculatorInterface $bearingCalculator
     * @param float $bearing
     */
    public function __construct(BearingCalculatorInterface $bearingCalculator, float $bearing)
    {
        $this->bearingCalculator = $bearingCalculator;
        $this->bearing = $bearing;
    }

    /**
     * @inheritDoc
     */
    public function simplify( LocationAggregateInterface|LocationInterface ...$locations ): array
    {
        $count = count($locations);
        if($count <= 3) return  $locations;

        $result = [];
        $i = 0;
        $result[] = $locations[$i];
        do {
            $i++;
            if ($i === $count - 1) {
                $result[] = $locations[$i];
                break;
            }

            $b1 = $this->bearingCalculator->calculateInitialBearing( $locations[$i - 1], $locations[$i] );
            $b2 = $this->bearingCalculator->calculateInitialBearing( $locations[$i], $locations[$i + 1] );

            $difference = min(fmod($b1 - $b2 + 360, 360), fmod($b2 - $b1 + 360, 360));

            if ($difference > $this->bearing)
                $result[] = $locations[$i];
        } while ($i < $count);

        return $result;
    }
}