<?php

namespace PhpGpxParser\Tests\Unit\Parser;

use PhpGpxParser\Parser\PointParser;
use PhpGpxParser\Parser\SegmentParser;
use PhpGpxParser\Models\Segment;
use PhpGpxParser\Models\TrackPoint;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class SegmentParserTest extends TestCase
{
    private SegmentParser $segmentParser;
    private PointParser $pointParser;

    protected function setUp(): void
    {
        $this->pointParser = $this->createMock(PointParser::class);
        $this->segmentParser = new SegmentParser($this->pointParser);
    }

    public function testParseSegmentWithNoPoints(): void
    {
        $xml = new SimpleXMLElement('<trkseg></trkseg>');

        $segment = $this->segmentParser->parseSegment($xml);

        $this->assertInstanceOf(Segment::class, $segment);
        $this->assertEquals(0, count($segment->getPoints()));
        $this->assertTrue($segment->isEmpty());
    }

    public function testParseSegmentWithMultiplePoints(): void
    {
        $xml = new SimpleXMLElement(
            '<trkseg>
                <trkpt lat="45.123" lon="5.456"></trkpt>
                <trkpt lat="45.124" lon="5.457"></trkpt>
                <trkpt lat="45.125" lon="5.458"></trkpt>
            </trkseg>'
        );

        $point1 = new TrackPoint(45.123, 5.456);
        $point2 = new TrackPoint(45.124, 5.457);
        $point3 = new TrackPoint(45.125, 5.458);

        // Configure le mock pour retourner les points attendus
        $this->pointParser->expects($this->exactly(3))
            ->method('parsePoint')
            ->willReturnOnConsecutiveCalls($point1, $point2, $point3);

        $segment = $this->segmentParser->parseSegment($xml);

        $this->assertInstanceOf(Segment::class, $segment);
        $this->assertEquals(3, count($segment->getPoints()));
        $this->assertFalse($segment->isEmpty());

        $points = $segment->getPoints();
        $this->assertSame($point1, $points[0]);
        $this->assertSame($point2, $points[1]);
        $this->assertSame($point3, $points[2]);
    }

    public function testParseSegmentWithPointsAndElevation(): void
    {
        $xml = new SimpleXMLElement(
            '<trkseg>
                <trkpt lat="45.123" lon="5.456"><ele>100.5</ele></trkpt>
                <trkpt lat="45.124" lon="5.457"><ele>101.5</ele></trkpt>
            </trkseg>'
        );

        $point1 = new TrackPoint(45.123, 5.456, 100.5);
        $point2 = new TrackPoint(45.124, 5.457, 101.5);

        $this->pointParser->expects($this->exactly(2))
            ->method('parsePoint')
            ->willReturnOnConsecutiveCalls($point1, $point2);

        $segment = $this->segmentParser->parseSegment($xml);

        $this->assertEquals(2, count($segment->getPoints()));

        $points = $segment->getPoints();
        $this->assertSame($point1, $points[0]);
        $this->assertSame($point2, $points[1]);
    }
}
