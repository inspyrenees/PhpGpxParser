<?php

namespace PhpGpxParser\Tests\Unit\Models;

use PhpGpxParser\Models\Track;
use PhpGpxParser\Models\Segment;
use PHPUnit\Framework\TestCase;

class TrackTest extends TestCase
{
    public function testInitialState(): void
    {
        $track = new Track();
        $this->assertEmpty($track->getSegments());
        $this->assertEquals('', $track->getName());
    }

    public function testSetName(): void
    {
        $track = new Track();
        $name = 'Test Track';

        $track->setName($name);
        $this->assertEquals($name, $track->getName());

        // Test with empty name
        $track->setName('');
        $this->assertEquals('', $track->getName());
    }

    public function testAddSegment(): void
    {
        $track = new Track();
        $segment1 = new Segment();
        $segment2 = new Segment();

        $track->addSegment($segment1);
        $this->assertCount(1, $track->getSegments());
        $this->assertSame($segment1, $track->getSegments()[0]);

        $track->addSegment($segment2);
        $this->assertCount(2, $track->getSegments());
        $this->assertSame($segment2, $track->getSegments()[1]);
    }

    public function testGetSegments(): void
    {
        $track = new Track();
        $segment1 = new Segment();
        $segment2 = new Segment();
        $segment3 = new Segment();

        $track->addSegment($segment1);
        $track->addSegment($segment2);
        $track->addSegment($segment3);

        $segments = $track->getSegments();
        $this->assertCount(3, $segments);
        $this->assertSame($segment1, $segments[0]);
        $this->assertSame($segment2, $segments[1]);
        $this->assertSame($segment3, $segments[2]);
    }
}
