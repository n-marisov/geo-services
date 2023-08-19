<?php

namespace Maris\Geo\Service\Finder;

use Maris\Geo\Service\Traits\LocationAggregatorConverterTrait;
use Maris\Interfaces\Geo\Factory\LocationFactoryInterface;
use Maris\Interfaces\Geo\Finder\IntermediateLocationFinderInterface;
use Maris\Interfaces\Geo\Finder\MidLocationFinderInterface;
use Maris\Interfaces\Geo\Aggregate\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;
use RuntimeException;

/***
 * Получает промежуточную точку
 */
class IntermediateLocationFinder implements IntermediateLocationFinderInterface, MidLocationFinderInterface
{

    use LocationAggregatorConverterTrait;


    protected LocationFactoryInterface $locationFactory;

    /**
     * @param LocationFactoryInterface $locationFactory
     */
    public function __construct(LocationFactoryInterface $locationFactory)
    {
        $this->locationFactory = $locationFactory;
    }


    /**
     * @inheritDoc
     */
    public function findIntermediateLocation(LocationAggregateInterface|LocationInterface $start, LocationAggregateInterface|LocationInterface $end, float $percent ): LocationInterface
    {
        $fraction = $percent / 100;

        $start = self::deg2radLocationToArray( $start );
        $end = self::deg2radLocationToArray( $end );
        $delta = [
            "lat" => $end["lat"] - $start["lat"],
            "long" => $end["long"] - $start["long"]
        ];

        if ($start["lat"] + $end["lat"] == 0.0 && abs($start["long"] - $end["long"]) == M_PI) {
            throw new RuntimeException(
                'Начальная и конечная точки являются антиподами, поэтому маршрут не определен.'
            );
        }

        $q = sin($delta["lat"] / 2) ** 2 + cos($start["lat"]) * cos($end["lat"]) * sin($delta["long"] / 2) ** 2;
        $delta = 2 * atan2(sqrt($q), sqrt(1 - $q));

        $a = sin((1 - $fraction) * $delta) / sin($delta);
        $b = sin($fraction * $delta) / sin($delta);

        $x = $a * cos($start["lat"]) * cos($start["long"]) + $b * cos($end["lat"]) * cos($end["long"]);
        $y = $a * cos($start["lat"]) * sin($start["long"]) + $b * cos($end["lat"]) * sin($end["long"]);
        $z = $a * sin($start["lat"]) + $b * sin($end["lat"]);

        return $this->locationFactory->new(
            rad2deg( atan2($z, sqrt($x ** 2 + $y ** 2)) ),
            rad2deg( atan2($y, $x) )
        );
    }

    /**
     * Получает среднюю точку.
     * @param LocationInterface|LocationAggregateInterface $start
     * @param LocationInterface|LocationAggregateInterface $end
     * @return LocationInterface
     */
    public function findMidLocation(LocationInterface|LocationAggregateInterface $start, LocationInterface|LocationAggregateInterface $end): LocationInterface
    {
        return $this->findIntermediateLocation( $start, $end, 50.0 );
    }
}