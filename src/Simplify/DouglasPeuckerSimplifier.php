<?php

namespace Maris\Geo\Service\Simplify;


use Maris\Interfaces\Geo\Calculator\PerpendicularDistanceCalculatorInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;
use Maris\Interfaces\Geo\Simplifier\SimplifierInterface;

/**
 * Упрощает список точек используя алгоритм Дугласа Пекера.
 *  @author Марисов Николай Андреевич.
 */
class DouglasPeuckerSimplifier implements SimplifierInterface
{
    /**
     * Калькулятор для расчета дистанции по перпендикуляру.
     * @var PerpendicularDistanceCalculatorInterface
     */
    protected PerpendicularDistanceCalculatorInterface $perpendicularDistanceCalculator;

    /**
     * Допуск по перпендикулярному расстоянию.
     * @var float
     */
    protected float $distance;

    /**
     * @param PerpendicularDistanceCalculatorInterface $perpendicularDistanceCalculator
     * @param float $distance
     */
    public function __construct(PerpendicularDistanceCalculatorInterface $perpendicularDistanceCalculator, float $distance)
    {
        $this->perpendicularDistanceCalculator = $perpendicularDistanceCalculator;
        $this->distance = $distance;
    }

    /**
     * Упрощает список точек.
     * @inheritDoc
     */
    public function simplify( LocationAggregateInterface ...$locations ): array
    {
        $dMax = 0;
        $index = 0;
        $count = count( $locations );
        $size = $count - 2;

        for ($i = 1; $i <= $size; $i++)
            if ( ($distance = $this->perpendicularDistanceCalculator->calculatePerpendicularDistance($locations[0], $locations[$count - 1],$locations[$i])) > $dMax ) {
                $index = $i;
                $dMax = $distance;
            }

        if ( isset($distance) && $dMax > $distance) {
            $lineSplitFirst = array_slice($locations, 0, $index + 1);
            $lineSplitSecond = array_slice($locations, $index, $count - $index);

            $resultsSplit1 = count($lineSplitFirst) > 2
                ? (new static( $this->perpendicularDistanceCalculator, $distance))->simplify( ...$lineSplitFirst )
                : $lineSplitFirst;

            $resultsSplit2 = count($lineSplitSecond) > 2
                ? (new static( $this->perpendicularDistanceCalculator, $distance))->simplify( ...$lineSplitSecond )
                : $lineSplitSecond;

            array_pop($resultsSplit1);

            return array_merge($resultsSplit1, $resultsSplit2);
        }

        return [ $locations[0], $locations[$count - 1] ];
    }
}