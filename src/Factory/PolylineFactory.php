<?php

namespace Maris\Geo\Service\Factory;


use Maris\Interfaces\Geo\AbstractModel\AbstractPolyline;
use Maris\Interfaces\Geo\Encoder\PolylineEncoderInterface;
use Maris\Interfaces\Geo\Factory\BoundsFactoryInterface;
use Maris\Interfaces\Geo\Factory\PolylineFactoryInterface;
use Maris\Interfaces\Geo\Model\BoundsInterface;
use Maris\Interfaces\Geo\Aggregate\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;
use Maris\Interfaces\Geo\Model\PolylineInterface;
use stdClass;

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

        return new class ( $coordinates, $this->boundsFactory ) extends AbstractPolyline
        {
            protected array $coordinates;

            protected BoundsFactoryInterface $boundsFactory;

            /**
             * @param array $coordinates
             * @param BoundsFactoryInterface $boundsFactory
             */
            public function __construct(array $coordinates, BoundsFactoryInterface $boundsFactory)
            {
                $this->coordinates = $coordinates;
                $this->boundsFactory = $boundsFactory;
            }


            public function getBounds(): BoundsInterface
            {
                return $this->boundsFactory->fromLocations( ... $this->coordinates );
            }

            public function add(LocationInterface|LocationAggregateInterface $location): PolylineInterface
            {
                $this->coordinates[] = $location;
                return $this;
            }

            public function get(int $position): LocationInterface|LocationAggregateInterface|null
            {
                return $this->coordinates[$position] ?? null;
            }

            public function remove(LocationInterface|int|LocationAggregateInterface $locationOrPosition): LocationInterface|LocationAggregateInterface|null
            {
                if(is_numeric($locationOrPosition))
                {
                    if(isset($this->coordinates[$locationOrPosition])){
                        $location = $this->coordinates[$locationOrPosition];
                        unset($this->coordinates[$locationOrPosition]);
                        return $location;
                    }
                    return null;
                }

                $position = array_search($locationOrPosition,$this->coordinates);

                if( $position !== false )
                    return $this->remove($position);

                return null;
            }

            public function toArray(): array
            {
                return $this->coordinates;
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