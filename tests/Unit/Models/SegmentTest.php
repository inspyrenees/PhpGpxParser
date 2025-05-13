<?php

namespace PhpGpxParser\Tests\Unit\Models;

use PhpGpxParser\Models\Segment;
use PhpGpxParser\Models\TrackPoint;
use PHPUnit\Framework\TestCase;

class SegmentTest extends TestCase
{
    public function testInitialState(): void
    {
        $segment = new Segment();
        $this->assertEmpty($segment->getPoints());
        $this->assertTrue($segment->isEmpty());
    }

    public function testAddPoint(): void
    {
        $segment = new Segment();
        $point1 = new TrackPoint(45.0, 5.0);
        $point2 = new TrackPoint(45.1, 5.1);

        $segment->addPoint($point1);
        $this->assertCount(1, $segment->getPoints());
        $this->assertSame($point1, $segment->getPoints()[0]);
        $this->assertFalse($segment->isEmpty());

        $segment->addPoint($point2);
        $this->assertCount(2, $segment->getPoints());
        $this->assertSame($point2, $segment->getPoints()[1]);
    }

    public function testGetPoints(): void
    {
        $segment = new Segment();
        $point1 = new TrackPoint(45.0, 5.0);
        $point2 = new TrackPoint(45.1, 5.1);
        $point3 = new TrackPoint(45.2, 5.2);

        $segment->addPoint($point1);
        $segment->addPoint($point2);
        $segment->addPoint($point3);

        $points = $segment->getPoints();
        $this->assertCount(3, $points);
        $this->assertSame($point1, $points[0]);
        $this->assertSame($point2, $points[1]);
        $this->assertSame($point3, $points[2]);
    }
}
