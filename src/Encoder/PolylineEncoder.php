<?php

namespace Maris\Geo\Service\Encoder;

use Generator;
use Maris\Interfaces\Geo\Encoder\PolylineEncoderInterface;
use Maris\Interfaces\Geo\Factory\LocationFactoryInterface;
use Maris\Interfaces\Geo\Factory\PolylineFactoryInterface;
use Maris\Interfaces\Geo\Model\PolylineInterface;

class PolylineEncoder implements PolylineEncoderInterface
{

    protected PolylineFactoryInterface $polylineFactory;

    protected LocationFactoryInterface $locationFactory;

    protected int $precision;

    /**
     * @param PolylineFactoryInterface $polylineFactory
     * @param LocationFactoryInterface $locationFactory
     * @param int $precision
     */
    public function __construct(PolylineFactoryInterface $polylineFactory, LocationFactoryInterface $locationFactory, int $precision)
    {
        $this->polylineFactory = $polylineFactory;
        $this->locationFactory = $locationFactory;
        $this->precision = $precision;
    }


    public function decode(string $encoded): PolylineInterface
    {

        for ( $i = 0, $j = 0,$pvs = [0,0],$f = []; $j < strlen($encoded); $i++ ){

            $s = $r = 0x00;
            do {
                $bit = ord(substr($encoded, $j++)) - 63;
                $r |= ( $bit & 0x1f ) << $s;
                $s += 5;
            } while ( $bit >= 0x20 );

            $pvs[$i % 2] = $pvs[$i % 2] + ( ($r & 1) ? ~($r >> 1) : ($r >> 1) );

            if( $i % 2 === 1)
                $f[] = $this->locationFactory->new(
                    $pvs[0] * ( 1 / pow(10, $this->precision ) ),
                    $pvs[1] * ( 1 / pow(10, $this->precision ) ),
                );
        }

       return $this->polylineFactory->new( ...$f );
    }

    public function encode(PolylineInterface $polyline): string
    {
        $encoded = "";
        $previous = [0,0];
        foreach ( $this->locationGenerator( $polyline ) as $position => $number )
            $encoded .= $this->encodeNumber( $position, $number, $previous );
        return $encoded;
    }


    /***
     * Кодирует одно значение координаты.
     * @param int $i
     * @param float $number
     * @param array $previous
     * @return string
     */
    protected function encodeNumber(int $i, float $number , array &$previous ):string
    {
        $number = (int) round($number * pow(10, $this->precision ) );
        $diff = $number - $previous[$i % 2];
        $previous[$i % 2] = $number;
        return $this->encodeChunk( ($diff < 0) ? ~($diff << 1) : ($diff << 1) );
    }

    /**
     * Кодирует число в строку.
     * @param float $number
     * @param string $chunk
     * @return string
     */
    protected function encodeChunk( float $number, string $chunk = "" ):string
    {
        while ( $number >= 0x20 ) {
            $chunk .= chr((0x20 | ($number & 0x1f)) + 63);
            $number >>= 5;
        }
        return $chunk . chr($number + 63);
    }

    /**
     * Генератор для последовательной переборки значений координат.
     * Позволяет не копировать в память массив с всеми значениями
     * координат полилинии
     * @param PolylineInterface $polyline
     * @return Generator
     */
    protected function locationGenerator( PolylineInterface $polyline ): Generator
    {
        foreach ($polyline as $item){
            $item = $item->getLocation();
            yield $item->getLatitude();
            yield $item->getLongitude();
        }
    }
}