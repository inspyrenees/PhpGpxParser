<?php

namespace PhpGpxParser\Tests\Unit\Utils;

use DateTimeImmutable;
use PhpGpxParser\Models\TrackPoint;
use PhpGpxParser\Utils\TimeCalculator;
use PHPUnit\Framework\TestCase;

class TimeCalculatorTest extends TestCase
{
    private TimeCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new TimeCalculator();
    }

    public function testCalculateEmptyPoints(): void
    {
        $this->calculator->calculate([]);

        $this->assertNull($this->calculator->getStart());
        $this->assertNull($this->calculator->getEnd());
        $this->assertEquals(0, $this->calculator->getDuration());
    }

    public function testCalculateSinglePoint(): void
    {
        $time = new DateTimeImmutable('2023-01-01 12:00:00');
        $point = new TrackPoint(45.5, 6.5, 1000.0, $time);

        $this->calculator->calculate([$point]);

        $this->assertEquals($time, $this->calculator->getStart());
        $this->assertEquals($time, $this->calculator->getEnd());
        $this->assertEquals(0, $this->calculator->getDuration());
    }

    public function testCalculatePointsWithNullTime(): void
    {
        $points = [
            new TrackPoint(45.5, 6.5, 1000.0, null),
            new TrackPoint(45.6, 6.6, 1100.0, null),
        ];

        $this->calculator->calculate($points);

        $this->assertNull($this->calculator->getStart());
        $this->assertNull($this->calculator->getEnd());
        $this->assertEquals(0, $this->calculator->getDuration());
    }

    public function testCalculateMultiplePoints(): void
    {
        $startTime = new DateTimeImmutable('2023-01-01 12:00:00');
        $endTime = new DateTimeImmutable('2023-01-01 13:30:00');

        $points = [
            new TrackPoint(45.5, 6.5, 1000.0, $startTime),
            new TrackPoint(45.6, 6.6, 1100.0, new DateTimeImmutable('2023-01-01 12:45:00')),
            new TrackPoint(45.7, 6.7, 1050.0, $endTime),
        ];

        $this->calculator->calculate($points);

        $this->assertEquals($startTime, $this->calculator->getStart());
        $this->assertEquals($endTime, $this->calculator->getEnd());
        // 1h30 = 5400 secondes
        $this->assertEquals(5400, $this->calculator->getDuration());
    }
}
