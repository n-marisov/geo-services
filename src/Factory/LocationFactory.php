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

    /**
     * @inheritDoc
     */
    public function fromString( string $coordinate ): ?LocationInterface
    {
        /**
         * Объединяем минуты и секунды.
         */
        $coordinate = preg_replace_callback(
            '/(\d+)(°|\s)\s*(\d+)(\'|′|\s)(\s*([0-9.]*))("|\'\'|″|′′)?/u',
            fn (array $matches): string => sprintf(
                '%d %f',
                $matches[1],
                (float)$matches[3] + (float)$matches[6] / 60
            )
            ,$coordinate
        );


        # "52 12.345, 13 23.456","52° 12.345, 13° 23.456", "52° 12.345′, 13° 23.456′", "52 12.345 N, 13 23.456 E","N52° 12.345′ E13° 23.456′"
        if (preg_match("/(-?\d{1,2})°?\s+(\d{1,2}\.?\d*)['′]?[, ]\s*(-?\d{1,3})°?\s+(\d{1,2}\.?\d*)['′]?/u", $coordinate, $match) === 1) {
            $latitude  = (int)$match[1] >= 0
                ? (int)$match[1] + (float)$match[2] / 60
                : (int)$match[1] - (float)$match[2] / 60;
            $longitude = (int)$match[3] >= 0
                ? (int)$match[3] + (float)$match[4] / 60
                : (int)$match[3] - (float)$match[4] / 60;

            return (is_numeric($latitude) && is_numeric($longitude))
                ? $this->new((float)$latitude, (float)$longitude)
                : null;
        }

        # "52 12.345, 13 23.456","52° 12.345, 13° 23.456", "52° 12.345′, 13° 23.456′", "52 12.345 N, 13 23.456 E","N52° 12.345′ E13° 23.456′"
        elseif (preg_match("/([NS]?\s*)(\d{1,2})°?\s+(\d{1,2}\.?\d*)['′]?(\s*[NS]?)[, ]\s*([EW]?\s*)(\d{1,3})°?\s+(\d{1,2}\.?\d*)['′]?(\s*[EW]?)/ui", $coordinate, $match) === 1) {
            $latitude = (int)$match[2] + (float)$match[3] / 60;
            if (strtoupper(trim($match[1])) === 'S' || strtoupper(trim($match[4])) === 'S') {
                $latitude = - $latitude;
            }
            $longitude = (int)$match[6] + (float)$match[7] / 60;
            if (strtoupper(trim($match[5])) === 'W' || strtoupper(trim($match[8])) === 'W') {
                $longitude = - $longitude;
            }
            return (is_numeric($latitude) && is_numeric($longitude))
                ? $this->new( (float)$latitude, (float)$longitude )
                : null;
        }
        # "65.5, 44.755544" или "46.42552 37.976"
        elseif (preg_match('/(-?\d{1,2}\.?\d*)°?[, ]\s*(-?\d{1,3}\.?\d*)°?/u', $coordinate, $match) === 1) {

            return (is_numeric($match[1]) && is_numeric($match[2]))
                ? $this->new( (float)$match[1], (float)$match[2] )
                : null;
        }

        #"40.2S, 135.3485W" или "56.234°N, 157.245°W"
        elseif (preg_match("/([NS]?\s*)(\d{1,2}\.?\d*)°?(\s*[NS]?)[, ]\s*([EW]?\s*)(\d{1,3}\.?\d*)°?(\s*[EW]?)/ui", $coordinate, $match) === 1) {
            $latitude = $match[2];
            if (strtoupper(trim($match[1])) === 'S' || strtoupper(trim($match[3])) === 'S') {
                $latitude = - $latitude;
            }
            $longitude = $match[5];
            if (strtoupper(trim($match[4])) === 'W' || strtoupper(trim($match[6])) === 'W') {
                $longitude = - $longitude;
            }

            return (is_numeric($latitude) && is_numeric($longitude))
                ? $this->new( (float)$latitude, (float)$longitude )
                : null;
        }
        return null;
    }
}