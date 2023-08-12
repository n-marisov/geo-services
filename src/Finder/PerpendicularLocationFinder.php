<?php

namespace Maris\Geo\Service\Finder;

use Maris\Interfaces\Geo\Factory\CartesianFactoryInterface;
use Maris\Interfaces\Geo\Factory\LocationFactoryInterface;
use Maris\Interfaces\Geo\Finder\PerpendicularLocationFinderInterface;
use Maris\Interfaces\Geo\Model\LocationAggregateInterface;
use Maris\Interfaces\Geo\Model\LocationInterface;

/**
 *
 */
class PerpendicularLocationFinder implements PerpendicularLocationFinderInterface
{

    /***
     * Радиус земного шара для расчетов.
     * @var float
     */
    protected readonly float $earthRadius;

    protected CartesianFactoryInterface $cartesianFactory;

    protected LocationFactoryInterface $locationFactory;

    /**
     * @param float $earthRadius
     * @param CartesianFactoryInterface $cartesianFactory
     * @param LocationFactoryInterface $locationFactory
     */
    public function __construct(float $earthRadius, CartesianFactoryInterface $cartesianFactory, LocationFactoryInterface $locationFactory)
    {
        $this->earthRadius = $earthRadius;
        $this->cartesianFactory = $cartesianFactory;
        $this->locationFactory = $locationFactory;
    }

    /**
     * @inheritDoc
     */
    public function findPerpendicularLocation(LocationAggregateInterface $start, LocationAggregateInterface $end, LocationAggregateInterface $point):LocationInterface
    {
        $a = $this->cartesianFactory->fromLocation( $start );
        $b = $this->cartesianFactory->fromLocation( $end );
        $p = $this->cartesianFactory->fromLocation( $point );

        $g = $this->cartesianFactory->new(
            $a->getY() * $b->getZ() - $a->getZ() * $b->getY(),
            $a->getZ() * $b->getX() - $a->getX() * $b->getZ(),
            $a->getX() * $b->getY() - $a->getY() * $b->getX()
        );
        $f = $this->cartesianFactory->new(
            $p->getY() * $g->getZ() - $p->getZ() * $g->getY(),
            $p->getZ() * $g->getX() - $p->getX() * $g->getZ(),
            $p->getX() * $g->getY() - $p->getY() * $g->getX()
        );
        $t = $this->cartesianFactory->new(
            $g->getY() * $f->getZ() - $g->getZ() * $f->getY(),
            $g->getZ() * $f->getX() - $g->getX() * $f->getZ(),
            $g->getX() * $f->getY() - $g->getY() * $f->getX()
        );

        $l = sqrt($t->getX() ** 2 + $t->getY() ** 2 + $t->getZ() ** 2 );

        return $this->locationFactory->fromCartesian( $this->cartesianFactory->new(
            $this->earthRadius * $t->getX() / $l,
            $this->earthRadius * $t->getY() / $l,
            $this->earthRadius * $t->getZ() / $l,
        ) );
    }

}