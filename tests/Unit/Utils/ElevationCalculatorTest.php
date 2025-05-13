<?php

namespace PhpGpxParser\Tests\Unit\Utils;

use PhpGpxParser\Models\TrackPoint;
use PhpGpxParser\Utils\ElevationCalculator;
use PHPUnit\Framework\TestCase;

class ElevationCalculatorTest extends TestCase
{
    private ElevationCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new ElevationCalculator();
    }

    public function testCalculateEmptyPoints(): void
    {
        $this->calculator->calculate([]);

        $this->assertEquals(0.0, $this->calculator->getGain());
        $this->assertEquals(0.0, $this->calculator->getLoss());
        $this->assertNull($this->calculator->getMin());
        $this->assertNull($this->calculator->getMax());
    }

    public function testCalculateSinglePoint(): void
    {
        $point = new TrackPoint(45.5, 6.5, 1000.0);
        $this->calculator->calculate([$point]);

        $this->assertEquals(0.0, $this->calculator->getGain());
        $this->assertEquals(0.0, $this->calculator->getLoss());
        $this->assertEquals(1000.0, $this->calculator->getMin());
        $this->assertEquals(1000.0, $this->calculator->getMax());
    }

    public function testCalculateWithNullElevation(): void
    {
        $points = [
            new TrackPoint(45.5, 6.5, 1000.0),
            new TrackPoint(45.6, 6.6, null),
        ];

        $this->calculator->calculate($points);

        $this->assertEquals(0.0, $this->calculator->getGain());
        $this->assertEquals(0.0, $this->calculator->getLoss());
        $this->assertEquals(1000.0, $this->calculator->getMin());
        $this->assertEquals(1000.0, $this->calculator->getMax());
    }

    public function testCalculateWithThreshold(): void
    {
        // Modifier temporairement le seuil
        $originalThreshold = ElevationCalculator::$thresholdElevation;
        ElevationCalculator::$thresholdElevation = 20;

        $points = [
            new TrackPoint(45.5, 6.5, 1000.0),
            new TrackPoint(45.6, 6.6, 1015.0), // Gain de 15m < seuil, devrait être ignoré
            new TrackPoint(45.7, 6.7, 990.0),  // Perte de 25m > seuil, devrait être comptée
        ];

        $this->calculator->calculate($points);

        $this->assertEquals(0.0, $this->calculator->getGain());
        $this->assertEquals(0.0, $this->calculator->getLoss());
        $this->assertEquals(990.0, $this->calculator->getMin());
        $this->assertEquals(1015.0, $this->calculator->getMax());

        // Restaurer le seuil
        ElevationCalculator::$thresholdElevation = $originalThreshold;
    }

    public function testCalculateGainAndLoss(): void
    {
        // Modifier temporairement le seuil
        $originalThreshold = ElevationCalculator::$thresholdElevation;
        ElevationCalculator::$thresholdElevation = 0; // Accepter toutes les variations

        $points = [
            new TrackPoint(45.5, 6.5, 1000.0),
            new TrackPoint(45.6, 6.6, 1100.0), // Gain de 100m
            new TrackPoint(45.7, 6.7, 1050.0), // Perte de 50m
            new TrackPoint(45.8, 6.8, 1200.0), // Gain de 150m
        ];

        $this->calculator->calculate($points);

        $this->assertEquals(250.0, $this->calculator->getGain());
        $this->assertEquals(50.0, $this->calculator->getLoss());
        $this->assertEquals(1000.0, $this->calculator->getMin());
        $this->assertEquals(1200.0, $this->calculator->getMax());

        // Restaurer le seuil
        ElevationCalculator::$thresholdElevation = $originalThreshold;
    }
}
