<?php

namespace PhpGpxParser\Tests\Unit\Models;

use PhpGpxParser\Models\GpxFile;
use PhpGpxParser\Models\Track;
use PhpGpxParser\Models\Segment;
use PhpGpxParser\Models\TrackPoint;
use PHPUnit\Framework\TestCase;

class GpxFileTest extends TestCase
{
    public function testInitialState(): void
    {
        $gpxFile = new GpxFile();
        $this->assertEmpty($gpxFile->getTracks());
        $this->assertTrue($gpxFile->isEmpty());
    }

    public function testAddTrack(): void
    {
        $gpxFile = new GpxFile();
        $track1 = new Track();
        $track2 = new Track();

        $gpxFile->addTrack($track1);
        $this->assertCount(1, $gpxFile->getTracks());
        $this->assertSame($track1, $gpxFile->getTracks()[0]);
        $this->assertFalse($gpxFile->isEmpty());

        $gpxFile->addTrack($track2);
        $this->assertCount(2, $gpxFile->getTracks());
        $this->assertSame($track2, $gpxFile->getTracks()[1]);
    }

    public function testGetAllTrackPoints(): void
    {
        $gpxFile = new GpxFile();

        // Create points
        $point1 = new TrackPoint(45.0, 5.0);
        $point2 = new TrackPoint(45.1, 5.1);
        $point3 = new TrackPoint(45.2, 5.2);
        $point4 = new TrackPoint(45.3, 5.3);

        // Create segments
        $segment1 = new Segment();
        $segment1->addPoint($point1);
        $segment1->addPoint($point2);

        $segment2 = new Segment();
        $segment2->addPoint($point3);

        // Create tracks
        $track1 = new Track();
        $track1->addSegment($segment1);

        $track2 = new Track();
        $track2->addSegment($segment2);

        // Add tracks to GPX file
        $gpxFile->addTrack($track1);
        $gpxFile->addTrack($track2);

        // Add another point to test merged results
        $segment3 = new Segment();
        $segment3->addPoint($point4);
        $track2->addSegment($segment3);

        // Get all points
        $allPoints = $gpxFile->getAllTrackPoints();

        // Verify results
        $this->assertCount(4, $allPoints);
        $this->assertSame($point1, $allPoints[0]);
        $this->assertSame($point2, $allPoints[1]);
        $this->assertSame($point3, $allPoints[2]);
        $this->assertSame($point4, $allPoints[3]);
    }

    public function testGetAllTrackPointsWithEmptyFile(): void
    {
        $gpxFile = new GpxFile();
        $this->assertEmpty($gpxFile->getAllTrackPoints());
    }
}
