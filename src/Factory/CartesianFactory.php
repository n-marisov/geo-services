<?php

namespace Maris\Geo\Service\Factory;

use Maris\Interfaces\Geo\AbstractModel\AbstractCartesian;
use Maris\Interfaces\Geo\Factory\CartesianFactoryInterface;
use Maris\Interfaces\Geo\Model\CartesianInterface;
use Maris\Interfaces\Geo\Aggregate\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

class CartesianFactory implements CartesianFactoryInterface
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
     * @inheritDoc
     */
    public function fromLocation( LocationAggregateInterface|LocationInterface $location ): CartesianInterface
    {
        $location = $location->getLocation();

        $latitude = deg2rad( 90 - $location->getLatitude() );
        $longitude = deg2rad( ($location->getLongitude() > 0) ? $location->getLongitude() : $location->getLongitude() + 360 );

        return $this->new(
            $this->earthRadius * cos( $longitude ) * sin( $latitude ),
            $this->earthRadius * sin( $longitude ) * sin( $latitude ),
            $this->earthRadius * cos( $latitude )
        );
    }

    public function new(float $x, float $y, float $z): CartesianInterface
    {
        return new class ($x,$y,$z) extends AbstractCartesian {};
    }
}