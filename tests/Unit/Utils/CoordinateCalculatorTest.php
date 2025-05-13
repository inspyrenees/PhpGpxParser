<?php

namespace PhpGpxParser\Tests\Unit\Utils;

use PhpGpxParser\Models\TrackPoint;
use PhpGpxParser\Utils\CoordinateCalculator;
use PHPUnit\Framework\TestCase;

class CoordinateCalculatorTest extends TestCase
{
    private CoordinateCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new CoordinateCalculator();
    }

    public function testCalculateEmptyPoints(): void
    {
        $this->calculator->calculate([]);

        // Vérifier que ça ne lève pas d'exception
        $this->assertTrue(true);

        // Les valeurs devraient être les valeurs par défaut
        $this->assertEquals([null, null], $this->calculator->getMin());
        $this->assertEquals([null, null], $this->calculator->getMax());
    }

    public function testCalculateSinglePoint(): void
    {
        $point = new TrackPoint(45.5, 6.5);
        $this->calculator->calculate([$point]);

        $this->assertEquals(45.5, $this->calculator->getStartLat());
        $this->assertEquals(6.5, $this->calculator->getStartLng());
        $this->assertEquals(45.5, $this->calculator->getEndLat());
        $this->assertEquals(6.5, $this->calculator->getEndLng());
        $this->assertEquals([45.5, 6.5], $this->calculator->getMin());
        $this->assertEquals([45.5, 6.5], $this->calculator->getMax());
    }

    public function testCalculateMultiplePoints(): void
    {
        $points = [
            new TrackPoint(45.5, 6.5),
            new TrackPoint(46.2, 6.0),
            new TrackPoint(45.8, 7.1),
        ];

        $this->calculator->calculate($points);

        // Vérifier les points de départ et d'arrivée
        $this->assertEquals(45.5, $this->calculator->getStartLat());
        $this->assertEquals(6.5, $this->calculator->getStartLng());
        $this->assertEquals(45.8, $this->calculator->getEndLat());
        $this->assertEquals(7.1, $this->calculator->getEndLng());

        // Vérifier les valeurs min/max
        $this->assertEquals([45.5, 6.0], $this->calculator->getMin());
        $this->assertEquals([46.2, 7.1], $this->calculator->getMax());
    }
}
