<?php

namespace Maris\Geo\Service\Factory;

use ArrayIterator;
use Maris\Interfaces\Geo\Encoder\PolylineEncoderInterface;
use Maris\Interfaces\Geo\Factory\BoundsFactoryInterface;
use Maris\Interfaces\Geo\Factory\PolylineFactoryInterface;
use Maris\Interfaces\Geo\Model\BoundsInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\PolylineInterface;
use stdClass;
use Traversable;

class PolylineFactory implements PolylineFactoryInterface
{

    protected BoundsFactoryInterface $boundsFactory;

    protected PolylineEncoderInterface $polylineEncoder;

    /**
     * @param BoundsFactoryInterface $boundsFactory
     * @param PolylineEncoderInterface $polylineEncoder
     */
    public function __construct(BoundsFactoryInterface $boundsFactory, PolylineEncoderInterface $polylineEncoder)
    {
        $this->boundsFactory = $boundsFactory;
        $this->polylineEncoder = $polylineEncoder;
    }

    /**
     * @inheritDoc
     */
    public function new(iterable $coordinates): PolylineInterface
    {
        return new class ( $coordinates ) implements PolylineInterface
        {
            protected array $coordinates = [];

            protected BoundsFactoryInterface $boundsFactory;

            /**
             * @param array<LocationAggregateInterface> $coordinates
             */
            public function __construct( iterable $coordinates, BoundsFactoryInterface $boundsFactory )
            {
                foreach ($coordinates as $coordinate)
                    if(is_a($coordinate,LocationAggregateInterface::class))
                        $this->coordinates[] = $coordinate;
                $this->boundsFactory = $boundsFactory;
            }

            public function getBounds(): BoundsInterface
            {
                return $this->boundsFactory->fromLocations( ...$this->coordinates );
            }

            public function getId(): ?int
            {
                return null;
            }

            public function getIterator(): Traversable
            {
                return new ArrayIterator( $this->coordinates );
            }
        };
    }

    public function fromJson(array|string|stdClass $coordinatesOrGeometry): ?PolylineInterface
    {
        if (is_string($coordinatesOrGeometry))
            $array = json_decode($coordinatesOrGeometry,1);
        elseif (is_object($coordinatesOrGeometry))
            $array = (array) $coordinatesOrGeometry;

        if(isset($array) && is_array($array)){

            if(isset($array["type"]) && $array["type"] == "LineString" && isset($array["coordinates"]))
                $array = $array["coordinates"];

            if(is_array($array) )
                return $this->new( $array );
        }
        return null;
    }
}