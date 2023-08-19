<?php

namespace Maris\Geo\Service\Factory;

use Maris\Interfaces\Geo\AbstractModel\AbstractBounds;
use Maris\Interfaces\Geo\Factory\BoundsFactoryInterface;
use Maris\Interfaces\Geo\Model\BoundsInterface;
use Maris\Interfaces\Geo\Aggregate\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

class BoundsFactory implements BoundsFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function fromLocations(LocationAggregateInterface|LocationInterface ...$locations): BoundsInterface
    {
        $latMin = 90.0;
        $latMax = -90.0;
        $lngMin = 180.0;
        $lngMax = -180.0;

        foreach ($locations as $location) {
            $latMin = min($location->getLatitude(), $latMin);
            $lngMin = min($location->getLocation(), $lngMin);
            $latMax = max($location->getLatitude(), $latMax);
            $lngMax = max($location->getLocation(), $lngMax);
        }

        return $this->new( $latMax, $lngMin, $latMin, $lngMax );
    }

    public function new(float $north, float $west, float $south, float $east): BoundsInterface
    {
        return new class($north,$west,$south,$east) extends AbstractBounds{};
    }
}