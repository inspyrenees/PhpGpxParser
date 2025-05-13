<?php

namespace PhpGpxParser\Tests\Unit\Utils;

use DateTimeImmutable;
use PhpGpxParser\Models\TrackPoint;
use PhpGpxParser\Utils\CoordinateCalculator;
use PhpGpxParser\Utils\DistanceCalculator;
use PhpGpxParser\Utils\ElevationCalculator;
use PhpGpxParser\Utils\GpxStatistics;
use PhpGpxParser\Utils\SpeedCalculator;
use PhpGpxParser\Utils\TimeCalculator;
use PHPUnit\Framework\TestCase;

class GpxStatisticsTest extends TestCase
{
    public function testConstructor(): void
    {
        $elevation = $this->createMock(ElevationCalculator::class);
        $distance = $this->createMock(DistanceCalculator::class);
        $speed = $this->createMock(SpeedCalculator::class);
        $time = $this->createMock(TimeCalculator::class);
        $coordinate = $this->createMock(CoordinateCalculator::class);

        $stats = new GpxStatistics($elevation, $distance, $speed, $time, $coordinate);

        $this->assertInstanceOf(GpxStatistics::class, $stats);
    }

    public function testFromTrackPoints(): void
    {
        $time1 = new DateTimeImmutable('2023-01-01 12:00:00');
        $time2 = new DateTimeImmutable('2023-01-01 13:00:00');

        $points = [
            new TrackPoint(45.0, 6.0, 1000.0, $time1),
            new TrackPoint(45.1, 6.0, 1100.0, $time2),
        ];

        $stats = GpxStatistics::fromTrackPoints($points);

        $this->assertInstanceOf(GpxStatistics::class, $stats);
    }

    public function testElevationMethods(): void
    {
        $elevation = $this->createMock(ElevationCalculator::class);
        $elevation->method('getGain')->willReturn(100.0);
        $elevation->method('getLoss')->willReturn(50.0);
        $elevation->method('getMin')->willReturn(950.0);
        $elevation->method('getMax')->willReturn(1100.0);

        $stats = new GpxStatistics(
            $elevation,
            $this->createMock(DistanceCalculator::class),
            $this->createMock(SpeedCalculator::class),
            $this->createMock(TimeCalculator::class),
            $this->createMock(CoordinateCalculator::class)
        );

        $this->assertEquals(100.0, $stats->getElevationGain());
        $this->assertEquals(50.0, $stats->getElevationLoss());
        $this->assertEquals(950.0, $stats->getMinElevation());
        $this->assertEquals(1100.0, $stats->getMaxElevation());
    }

    public function testDistanceMethods(): void
    {
        $distance = $this->createMock(DistanceCalculator::class);
        $distance->method('getTotal')->willReturn(15000.0); // 15km

        $stats = new GpxStatistics(
            $this->createMock(ElevationCalculator::class),
            $distance,
            $this->createMock(SpeedCalculator::class),
            $this->createMock(TimeCalculator::class),
            $this->createMock(CoordinateCalculator::class)
        );

        $this->assertEquals(15000.0, $stats->getTotalDistance());
    }

    public function testSpeedMethods(): void
    {
        $speed = $this->createMock(SpeedCalculator::class);
        $speed->method('getAvg')->willReturn(12.5);
        $speed->method('getMax')->willReturn(20.3);

        $stats = new GpxStatistics(
            $this->createMock(ElevationCalculator::class),
            $this->createMock(DistanceCalculator::class),
            $speed,
            $this->createMock(TimeCalculator::class),
            $this->createMock(CoordinateCalculator::class)
        );

        $this->assertEquals(12.5, $stats->getAvgSpeed());
        $this->assertEquals(20.3, $stats->getMaxSpeed());
    }

    public function testTimeMethods(): void
    {
        $startTime = new DateTimeImmutable('2023-01-01 12:00:00');
        $endTime = new DateTimeImmutable('2023-01-01 14:30:00');

        $time = $this->createMock(TimeCalculator::class);
        $time->method('getStart')->willReturn($startTime);
        $time->method('getEnd')->willReturn($endTime);
        $time->method('getDuration')->willReturn(9000); // 2h30 = 9000 secondes

        $stats = new GpxStatistics(
            $this->createMock(ElevationCalculator::class),
            $this->createMock(DistanceCalculator::class),
            $this->createMock(SpeedCalculator::class),
            $time,
            $this->createMock(CoordinateCalculator::class)
        );

        $this->assertEquals($startTime, $stats->getStartTime());
        $this->assertEquals($endTime, $stats->getEndTime());
        $this->assertEquals(9000, $stats->getDuration());
    }

    public function testCoordinateMethods(): void
    {
        $coordinate = $this->createMock(CoordinateCalculator::class);
        $coordinate->method('getStartLat')->willReturn(45.0);
        $coordinate->method('getStartLng')->willReturn(6.0);
        $coordinate->method('getEndLat')->willReturn(45.2);
        $coordinate->method('getEndLng')->willReturn(6.3);
        $coordinate->method('getMin')->willReturn([44.9, 5.9]);
        $coordinate->method('getMax')->willReturn([45.3, 6.4]);

        $stats = new GpxStatistics(
            $this->createMock(ElevationCalculator::class),
            $this->createMock(DistanceCalculator::class),
            $this->createMock(SpeedCalculator::class),
            $this->createMock(TimeCalculator::class),
            $coordinate
        );

        $this->assertEquals(45.0, $stats->getStartLat());
        $this->assertEquals(6.0, $stats->getStartLng());
        $this->assertEquals(45.2, $stats->getEndLat());
        $this->assertEquals(6.3, $stats->getEndLng());
        $this->assertEquals([44.9, 5.9], $stats->getMinCoordinates());
        $this->assertEquals([45.3, 6.4], $stats->getMaxCoordinates());
    }
}
