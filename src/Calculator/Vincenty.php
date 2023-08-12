<?php

namespace Maris\Geo\Service\Calculator;

use Maris\Interfaces\Geo\Factory\LocationFactoryInterface;
use Maris\Interfaces\Geo\Calculator\BearingCalculatorInterface;
use Maris\Interfaces\Geo\Calculator\DistanceCalculatorInterface;
use Maris\Interfaces\Geo\Finder\DestinationFinderInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

/**
 * Калькулятор Vincenty.
 * Позволят посчитать расстояние между точками,
 * азимуты между точками и найти точку на определенном
 * расстоянии на заданном начальном азимуте от заданной точки.
 * @author Марисов Николай Андреевич
 */
class Vincenty implements DestinationFinderInterface, DistanceCalculatorInterface,BearingCalculatorInterface
{

    protected const M_2_PI = M_PI * 2;
    protected const M_3_PI = M_PI * 3;

    /**
     * Фабрика координат.
     * @var LocationFactoryInterface
     */
    private LocationFactoryInterface $locationFactory;

    /**
     * Радиус на экваторе.
     * @var float
     */
    protected float $equatorialRadius; //b

    /**
     * Радиус на полюсах
     * @var float
     */
    protected float $polarRadius; //a

    /**
     * Степень сжатия
     * @var float
     */
    protected float $flattening; // f

    /**
     * Максимальное число итераций.
     * @var int
     */
    protected int $iMax = 300;

    /**
     * @param LocationFactoryInterface $locationFactory
     * @param float $equatorialRadius
     * @param float $polarRadius
     * @param int $iMax
     */
    public function __construct( LocationFactoryInterface $locationFactory, float $equatorialRadius, float $polarRadius, int $iMax = 300 )
    {
        $this->locationFactory = $locationFactory;

        $this->equatorialRadius = $equatorialRadius;
        $this->polarRadius = $polarRadius;

        $this->flattening = 1 / (( $polarRadius - $equatorialRadius ) / $polarRadius);

        $this->iMax = $iMax;
    }


    public function calculateDistance(LocationAggregateInterface $start, LocationAggregateInterface $end): float
    {
        return $this->inverse( $start->getLocation(), $end->getLocation() )["distance"];
    }

    public function findDestination(LocationAggregateInterface $location, float $initialBearing, float $distance): LocationInterface
    {
        return $this->locationFactory->new(
            ...$this->direct(
                $location->getLocation(), $initialBearing, $distance
            )["destination"]
        );
    }

    public function calculateInitialBearing(LocationAggregateInterface $start, LocationAggregateInterface $end): float
    {
        return $this->inverse( $start->getLocation(), $end->getLocation() )["bearing"]["initial"];
    }

    public function calculateFinalBearing(LocationAggregateInterface $start, LocationAggregateInterface $end): float
    {
        return $this->inverse( $start->getLocation(), $end->getLocation() )["bearing"]["final"];
    }

    /**
     * Вычисляет ряд А
     * @param float $k
     * @return float
     */
    protected function calcA( float $k ):float
    {
        return (1 +  $k ** 2 / 4) / (1-$k);
    }

    /***
     * Вычисляет ряд В
     * @param float $k
     * @return float
     */
    protected function calcB( float $k ):float
    {
        return $k * ( 1 - 3 * $k ** 2 / 8);
    }

    /**
     * Вычисляет коэффициент для расчета рядов А и В
     * @param float $uSq
     * @return float
     */
    protected function calcK( float $uSq ):float
    {
        return ( ($s = sqrt(1 + $uSq )) - 1 ) / ( $s + 1 );
    }

    /**
     * Вычисляет параметр С.
     * @param float $cosSquAlpha
     * @return float
     */
    protected function calcC( float $cosSquAlpha ):float
    {
        return $this->flattening / 16 * $cosSquAlpha * ( 4 + $this->flattening  * (4 - 3 * $cosSquAlpha) );
    }
    /**
     * Вычисляет U в квадрате.
     * @param $cosSquareAlpha
     * @return float
     */
    protected function calcUSquare( $cosSquareAlpha ):float
    {
        $squareB = $this->equatorialRadius ** 2 ;
        return $cosSquareAlpha * ($this->polarRadius ** 2 - $squareB) / $squareB;
    }

    /**
     * @param float $B
     * @param float $sinSigma
     * @param float $cosSigma
     * @param float $cos2SigmaM
     * @return float
     */
    protected function calcDeltaSigma(float $B, float $sinSigma, float $cosSigma, float $cos2SigmaM):float
    {
        return $B * $sinSigma * ($cos2SigmaM + $B / 4
                * ($cosSigma * (-1 + 2 * $cos2SigmaM * $cos2SigmaM) - $B / 6
                    * $cos2SigmaM * (-3 + 4 * $sinSigma * $sinSigma)
                    * (-3 + 4 * $cos2SigmaM * $cos2SigmaM)
                )
            );
    }

    /**
     * Обратная задача
     * @param LocationInterface $start
     * @param LocationInterface $end
     * @return array{distance:float,bearing:array{initial:float,final:float}}
     */
    public function inverse( LocationInterface $start, LocationInterface $end ):array
    {

        $startLat = deg2rad( $start->getLatitude() );
        $endLat = deg2rad($end->getLatitude());
        $startLon = deg2rad( $start->getLongitude() );
        $endLon = deg2rad($end->getLongitude());

        $f = $this->flattening;

        $L = $endLon - $startLon;

        $tanU1 = (1 - $f) * tan($startLat);
        $cosU1 = 1 / sqrt(1 + $tanU1 * $tanU1);
        $sinU1 = $tanU1 * $cosU1;
        $tanU2 = (1 - $f) * tan($endLat);
        $cosU2 = 1 / sqrt(1 + $tanU2 * $tanU2);
        $sinU2 = $tanU2 * $cosU2;

        $lambda = $L;

        $iterations = 0;

        do {
            $sinLambda = sin($lambda);
            $cosLambda = cos($lambda);
            $sinSqSigma = ($cosU2 * $sinLambda) * ($cosU2 * $sinLambda)
                + ($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda) * ($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda);
            $sinSigma = sqrt($sinSqSigma);

            if ($sinSigma == 0) return [
                    "distance" => 0,
                    "bearing" => [
                        "initial" => 0,
                        "final" => 0
                    ]
                ];

            $cosSigma = $sinU1 * $sinU2 + $cosU1 * $cosU2 * $cosLambda;
            $sigma = atan2($sinSigma, $cosSigma);
            $sinAlpha = $cosU1 * $cosU2 * $sinLambda / $sinSigma;
            $cosSquAlpha = 1 - $sinAlpha * $sinAlpha;

            /**
             * Устанавливаем на 0 на случай экваториальных линий
             */
            $cos2SigmaM = ($cosSquAlpha !== 0.0) ? $cosSigma - 2 * $sinU1 * $sinU2 / $cosSquAlpha : 0;


            $C = $this->calcC( $cosSquAlpha );

            $lambdaP = $lambda;
            $lambda = $L + (1 - $C) * $f * $sinAlpha
                * ($sigma + $C * $sinSigma * ($cos2SigmaM + $C * $cosSigma * (-1 + 2 * $cos2SigmaM * $cos2SigmaM)));
            $iterations++;
        } while ( abs($lambda - $lambdaP) > 1E-12 && $this->iMax > $iterations);

        //if ($iterations >= 200) {
           // throw new NotConvergingException('Inverse EllipsoidalCalculator Formula did not converge');
        //}

        $uSq = $this->calcUSquare( $cosSquAlpha );
        $K = $this->calcK( $uSq );
        $A = $this->calcA( $K );
        $B = $this->calcB( $K );

        $a1 = atan2($cosU2 * $sinLambda, $cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda);
        $a2 = atan2($cosU1 * $sinLambda, -$sinU1 * $cosU2 + $cosU1 * $sinU2 * $cosLambda);

        $a1 = fmod($a1 + self::M_2_PI, self::M_2_PI);
        $a2 = fmod($a2 + self::M_2_PI, self::M_2_PI);

        return [
            "distance" => $this->equatorialRadius * $A * ( $sigma - $this->calcDeltaSigma( $B, $sinSigma, $cosSigma, $cos2SigmaM )),
            "bearing" =>[
                "initial" => rad2deg($a1),
                "final" => rad2deg($a2)
            ]
        ];
    }

    /**
     * Реализация прямой задачи.
     * @param LocationInterface $start
     * @param float $initialBearing
     * @param float $distance
     * @return array
     */
    public function direct( LocationInterface $start , float $initialBearing, float $distance ):array
    {
        $phi1 = deg2rad( $start->getLatitude() );
        $lambda1 = deg2rad( $start->getLongitude() );
        $alpha1 = deg2rad( $initialBearing );

        $sinAlpha1 = sin( $alpha1 );
        $cosAlpha1 = cos( $alpha1 );

        $tanU1 = ( 1 - $this->flattening ) * tan($phi1);
        $cosU1 = 1 / sqrt(1 + $tanU1 * $tanU1);
        $sinU1 = $tanU1 * $cosU1;
        $sigma1 = atan2($tanU1, $cosAlpha1);
        $sinAlpha = $cosU1 * $sinAlpha1;
        $cosSquAlpha = 1 - $sinAlpha * $sinAlpha;


        $K = $this->calcK( $this->calcUSquare( $cosSquAlpha ) );
        $A = $this->calcA( $K );
        $B = $this->calcB( $K );


        $sigmaS = $distance / ( $this->equatorialRadius * $A );
        $iterations = 0;
        do{
            $cos2SigmaM = cos(2 * $sigma1 + ($sigma ?? $sigma = $sigmaS) );
            $sinSigma = sin($sigma);
            $cosSigma = cos($sigma);
            $deltaSigma = $this->calcDeltaSigma( $B,  $sinSigma,  $cosSigma,  $cos2SigmaM );
            $sigmaS = $sigma;
            $sigma = $distance / ($this->equatorialRadius * $A) + $deltaSigma;
            $iterations++;
        }while( abs($sigma - $sigmaS) > 1E-12 && $this->iMax > $iterations );



        $tmp = $sinU1 * $sinSigma - $cosU1 * $cosSigma * $cosAlpha1;
        $phi2 = atan2(
            $sinU1 * $cosSigma + $cosU1 * $sinSigma * $cosAlpha1,
            (1 - $this->flattening) * sqrt($sinAlpha * $sinAlpha + $tmp * $tmp)
        );
        $lambda = atan2($sinSigma * $sinAlpha1, $cosU1 * $cosSigma - $sinU1 * $sinSigma * $cosAlpha1);

        $C = $this->calcC( $cosSquAlpha );

        $L = $lambda
            - (1 - $C) * $this->flattening * $sinAlpha
            * ($sigma + $C * $sinSigma * ($cos2SigmaM + $C * $cosSigma * (-1 + 2 * $cos2SigmaM ** 2)));

        $lambda2 = fmod($lambda1 + $L + self::M_3_PI, self::M_2_PI ) - M_PI;

        $alpha2 = atan2( $sinAlpha, -$tmp );
        $alpha2 = fmod($alpha2 + self::M_2_PI , self::M_2_PI );

        return [
            "destination" =>[ rad2deg( $phi2 ), rad2deg( $lambda2 ) ],
            "finalBearing" => rad2deg( $alpha2 )
        ];

    }
}