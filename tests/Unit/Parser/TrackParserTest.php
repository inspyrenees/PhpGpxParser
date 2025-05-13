<?php

namespace PhpGpxParser\Tests\Unit\Parser;

use PhpGpxParser\Parser\TrackParser;
use PhpGpxParser\Parser\SegmentParser;
use PhpGpxParser\Models\Track;
use PhpGpxParser\Models\Segment;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class TrackParserTest extends TestCase
{
    private TrackParser $trackParser;
    private SegmentParser $segmentParser;

    protected function setUp(): void
    {
        $this->segmentParser = $this->createMock(SegmentParser::class);
        $this->trackParser = new TrackParser($this->segmentParser);
    }

    public function testParseTrackWithNoSegments(): void
    {
        $xml = new SimpleXMLElement('<trk><name>Test Track</name></trk>');

        $track = $this->trackParser->parseTrack($xml);

        $this->assertInstanceOf(Track::class, $track);
        $this->assertEquals('Test Track', $track->getName());
        $this->assertEquals(0, count($track->getSegments()));
    }

    public function testParseTrackWithNoName(): void
    {
        $xml = new SimpleXMLElement('<trk></trk>');

        $track = $this->trackParser->parseTrack($xml);

        $this->assertInstanceOf(Track::class, $track);
        $this->assertEquals('', $track->getName());
    }

    public function testParseTrackWithMultipleSegments(): void
    {
        $xml = new SimpleXMLElement(
            '<trk>
                <name>Mountain Trail</name>
                <trkseg></trkseg>
                <trkseg></trkseg>
                <trkseg></trkseg>
            </trk>'
        );

        $segment1 = new Segment();
        $segment2 = new Segment();
        $segment3 = new Segment();

        // Configure le mock pour retourner les segments attendus
        $this->segmentParser->expects($this->exactly(3))
            ->method('parseSegment')
            ->willReturnOnConsecutiveCalls($segment1, $segment2, $segment3);

        $track = $this->trackParser->parseTrack($xml);

        $this->assertInstanceOf(Track::class, $track);
        $this->assertEquals('Mountain Trail', $track->getName());
        $this->assertEquals(3, count($track->getSegments()));

        $segments = $track->getSegments();
        $this->assertSame($segment1, $segments[0]);
        $this->assertSame($segment2, $segments[1]);
        $this->assertSame($segment3, $segments[2]);
    }

    public function testParseTrackWithSpecialCharactersInName(): void
    {
        $xml = new SimpleXMLElement('<trk><name>Col du Galibier &amp; Alpe d\'Huez</name></trk>');

        $track = $this->trackParser->parseTrack($xml);

        $this->assertEquals('Col du Galibier & Alpe d\'Huez', $track->getName());
    }
}
