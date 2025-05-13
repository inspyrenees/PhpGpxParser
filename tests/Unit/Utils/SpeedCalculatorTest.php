<?php

namespace PhpGpxParser\Tests\Unit\Utils;

use DateTimeImmutable;
use PhpGpxParser\Models\TrackPoint;
use PhpGpxParser\Utils\SpeedCalculator;
use PHPUnit\Framework\TestCase;

class SpeedCalculatorTest extends TestCase
{
    private SpeedCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new SpeedCalculator();
    }

    public function testCalculateEmptyPoints(): void
    {
        $this->calculator->calculate([]);

        $this->assertEquals(0.0, $this->calculator->getMax());
        $this->assertEquals(0.0, $this->calculator->getAvg());
    }

    public function testCalculateSinglePoint(): void
    {
        $time = new DateTimeImmutable('2023-01-01 12:00:00');
        $point = new TrackPoint(45.5, 6.5, 1000.0, $time);

        $this->calculator->calculate([$point]);

        $this->assertEquals(0.0, $this->calculator->getMax());
        $this->assertEquals(0.0, $this->calculator->getAvg());
    }

    public function testCalculatePointsWithoutTime(): void
    {
        $points = [
            new TrackPoint(45.5, 6.5, 1000.0),
            new TrackPoint(45.6, 6.6, 1100.0),
        ];

        $this->calculator->calculate($points);

        $this->assertEquals(0.0, $this->calculator->getMax());
        $this->assertEquals(0.0, $this->calculator->getAvg());
    }

    public function testCalculateIdenticalPoints(): void
    {
        $startTime = new DateTimeImmutable('2023-01-01 12:00:00');
        $endTime = new DateTimeImmutable('2023-01-01 12:30:00');

        $points = [
            new TrackPoint(45.5, 6.5, 1000.0, $startTime),
            new TrackPoint(45.5, 6.5, 1000.0, $endTime),
        ];

        $this->calculator->calculate($points);

        $this->assertEquals(0.0, $this->calculator->getMax());
        $this->assertEquals(0.0, $this->calculator->getAvg());
    }

    public function testCalculateWithConstantSpeed(): void
    {
        // 10km en 1 heure = 10 km/h
        $startTime = new DateTimeImmutable('2023-01-01 12:00:00');
        $endTime = new DateTimeImmutable('2023-01-01 13:00:00');

        // Points séparés d'environ 10km
        $points = [
            new TrackPoint(45.0, 6.0, 1000.0, $startTime),
            new TrackPoint(45.1, 6.0, 1000.0, $endTime),  // ~11km au nord
        ];

        $this->calculator->calculate($points);

        // La vitesse devrait être d'environ 11 km/h
        $this->assertGreaterThan(10.0, $this->calculator->getMax());
        $this->assertLessThan(12.0, $this->calculator->getMax());
        $this->assertEquals($this->calculator->getMax(), $this->calculator->getAvg());
    }

    public function testCalculateWithVariableSpeed(): void
    {
        // Environ 10km en 30min puis 5km en 1h
        $time1 = new DateTimeImmutable('2023-01-01 12:00:00');
        $time2 = new DateTimeImmutable('2023-01-01 12:30:00'); // 30min plus tard
        $time3 = new DateTimeImmutable('2023-01-01 13:30:00'); // 1h plus tard

        $points = [
            new TrackPoint(45.0, 6.0, 1000.0, $time1),
            new TrackPoint(45.1, 6.0, 1000.0, $time2), // ~11km au nord, 30min = ~22km/h
            new TrackPoint(45.1, 6.05, 1000.0, $time3), // ~4km à l'est, 1h = ~4km/h
        ];

        $this->calculator->calculate($points);

        // Vitesse max devrait être autour de 22 km/h
        $this->assertGreaterThan(20.0, $this->calculator->getMax());
        $this->assertLessThan(24.0, $this->calculator->getMax());

        // Vitesse moyenne devrait être autour de (11+4)/(0.5+1) = 10 km/h
        $this->assertGreaterThan(9.0, $this->calculator->getAvg());
        $this->assertLessThan(11.0, $this->calculator->getAvg());
    }
}
