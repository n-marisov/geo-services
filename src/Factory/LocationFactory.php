<?php

namespace Maris\Geo\Service\Factory;

use Maris\Interfaces\Geo\Factory\LocationFactoryInterface;
use Maris\Interfaces\Geo\Model\CartesianInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

/**
 * Фабрика для создания координат
 */
class LocationFactory implements LocationFactoryInterface
{

    /***
     * Радиус земного шара для расчетов.
     * @var float
     */
    protected readonly float $earthRadius;

    /**
     * @param float $earthRadius
     */
    public function __construct(float $earthRadius)
    {
        $this->earthRadius = $earthRadius;
    }

    /**
     * @inheritDoc
     */
    public function fromCartesian( CartesianInterface $cartesian ): LocationInterface
    {
        return $this->new(
            rad2deg( asin($cartesian->getZ()/ $this->earthRadius )),
            rad2deg( atan2( $cartesian->getY() , $cartesian->getX() ))
        );
    }

    /**
     * @inheritDoc
     */
    public function new(float $latitude, float $longitude): LocationInterface
    {
        return new class ( $latitude, $longitude ) implements LocationInterface
        {
            private float $latitude;
            private float $longitude;
            public function __construct(float $latitude, float $longitude)
            {
                $this->latitude = $latitude;
                $this->longitude = $longitude;
            }
            public function getLatitude(): float
            {
                return $this->latitude;
            }
            public function getLongitude(): float
            {
                return $this->longitude;
            }
        };
    }
}