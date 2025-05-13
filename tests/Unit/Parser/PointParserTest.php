<?php

namespace PhpGpxParser\Tests\Unit\Parser;

use PhpGpxParser\Parser\PointParser;
use PhpGpxParser\Models\TrackPoint;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class PointParserTest extends TestCase
{
    private PointParser $pointParser;

    protected function setUp(): void
    {
        $this->pointParser = new PointParser();
    }

    public function testParsePointWithBasicAttributes(): void
    {
        $xml = new SimpleXMLElement('<trkpt lat="45.123" lon="5.456"></trkpt>');

        $point = $this->pointParser->parsePoint($xml);

        $this->assertInstanceOf(TrackPoint::class, $point);
        $this->assertEquals(45.123, $point->getLatitude());
        $this->assertEquals(5.456, $point->getLongitude());
        $this->assertNull($point->getElevation());
        $this->assertNull($point->getTime());
    }

    public function testParsePointWithAllAttributes(): void
    {
        $xml = new SimpleXMLElement(
            '<trkpt lat="48.858844" lon="2.294351">
                <ele>35.5</ele>
                <time>2023-01-01T12:00:00Z</time>
            </trkpt>'
        );

        $point = $this->pointParser->parsePoint($xml);

        $this->assertInstanceOf(TrackPoint::class, $point);
        $this->assertEquals(48.858844, $point->getLatitude());
        $this->assertEquals(2.294351, $point->getLongitude());
        $this->assertEquals(35.5, $point->getElevation());
        $this->assertInstanceOf(\DateTimeImmutable::class, $point->getTime());
        $this->assertEquals('2023-01-01T12:00:00+00:00', $point->getTime()->format(DATE_ATOM));
    }

    public function testParsePointWithElevationOnly(): void
    {
        $xml = new SimpleXMLElement(
            '<trkpt lat="45.123" lon="5.456">
                <ele>1200.5</ele>
            </trkpt>'
        );

        $point = $this->pointParser->parsePoint($xml);

        $this->assertEquals(45.123, $point->getLatitude());
        $this->assertEquals(5.456, $point->getLongitude());
        $this->assertEquals(1200.5, $point->getElevation());
        $this->assertNull($point->getTime());
    }

    public function testParsePointWithTimeOnly(): void
    {
        $xml = new SimpleXMLElement(
            '<trkpt lat="45.123" lon="5.456">
                <time>2023-01-01T12:00:00Z</time>
            </trkpt>'
        );

        $point = $this->pointParser->parsePoint($xml);

        $this->assertEquals(45.123, $point->getLatitude());
        $this->assertEquals(5.456, $point->getLongitude());
        $this->assertNull($point->getElevation());
        $this->assertEquals('2023-01-01T12:00:00+00:00', $point->getTime()->format(DATE_ATOM));
    }
}
