<?php

namespace PhpGpxParser\Tests\Unit\Models;

use PhpGpxParser\Models\TrackPoint;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class TrackPointTest extends TestCase
{
    public function testConstructor(): void
    {
        $latitude = 45.123;
        $longitude = 5.456;
        $elevation = 1200.5;
        $time = new DateTimeImmutable('2023-01-01T12:00:00Z');

        $point = new TrackPoint($latitude, $longitude, $elevation, $time);

        $this->assertEquals($latitude, $point->getLatitude());
        $this->assertEquals($longitude, $point->getLongitude());
        $this->assertEquals($elevation, $point->getElevation());
        $this->assertEquals($time, $point->getTime());
    }

    public function testConstructorWithMinimalParameters(): void
    {
        $latitude = 48.858844;
        $longitude = 2.294351;

        $point = new TrackPoint($latitude, $longitude);

        $this->assertEquals($latitude, $point->getLatitude());
        $this->assertEquals($longitude, $point->getLongitude());
        $this->assertNull($point->getElevation());
        $this->assertNull($point->getTime());
    }

    public function testSetElevation(): void
    {
        $point = new TrackPoint(45.0, 5.0);
        $this->assertNull($point->getElevation());

        $elevation = 1500.75;
        $point->setElevation($elevation);
        $this->assertEquals($elevation, $point->getElevation());

        // Test with null value
        $point->setElevation(null);
        $this->assertNull($point->getElevation());
    }
}
