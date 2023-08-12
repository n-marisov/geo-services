<?php

namespace Maris\Geo\Service\Finder;

use Maris\Interfaces\Geo\Factory\LocationFactoryInterface;
use Maris\Interfaces\Geo\Finder\IntermediateLocationFinderInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;
use RuntimeException;

class IntermediateLocationFinder implements IntermediateLocationFinderInterface
{

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
    public function findIntermediateLocation(LocationAggregateInterface $start, LocationAggregateInterface $end, float $percent ): LocationInterface
    {

        $start = $start->getLocation();
        $end = $end->getLocation();

        $fraction = $percent / 100;

        $lat1 = deg2rad( $start->getLatitude() );
        $lng1 = deg2rad( $start->getLongitude() );
        $lat2 = deg2rad( $end->getLatitude() );
        $lng2 = deg2rad( $end->getLongitude() );
        $deltaLat = $lat2 - $lat1;
        $deltaLng = $lng2 - $lng1;

        if ($lat1 + $lat2 == 0.0 && abs($lng1 - $lng2) == M_PI) {
            throw new RuntimeException(
                'Начальная и конечная точки являются антиподами, поэтому маршрут не определен.'
            );
        }

        $q = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLng / 2) ** 2;
        $delta = 2 * atan2(sqrt($q), sqrt(1 - $q));

        $a = sin((1 - $fraction) * $delta) / sin($delta);
        $b = sin($fraction * $delta) / sin($delta);

        $x = $a * cos($lat1) * cos($lng1) + $b * cos($lat2) * cos($lng2);
        $y = $a * cos($lat1) * sin($lng1) + $b * cos($lat2) * sin($lng2);
        $z = $a * sin($lat1) + $b * sin($lat2);

        return $this->locationFactory->new(
            rad2deg( atan2($z, sqrt($x ** 2 + $y ** 2)) ),
            rad2deg( atan2($y, $x) )
        );
    }
}