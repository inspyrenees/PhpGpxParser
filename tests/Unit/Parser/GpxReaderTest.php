<?php

namespace PhpGpxParser\Tests\Unit\Parser;

use PHPUnit\Framework\TestCase;
use PhpGpxParser\Parser\GpxReader;
use PhpGpxParser\Parser\TrackParser;
use PhpGpxParser\Parser\SegmentParser;
use PhpGpxParser\Parser\PointParser;
use PhpGpxParser\Models\GpxFile;
use PhpGpxParser\Exception\GpxParserException;

class GpxReaderTest extends TestCase
{
    private GpxReader $reader;
    private TrackParser $trackParser;

    protected function setUp(): void
    {
        $pointParser = new PointParser();
        $segmentParser = new SegmentParser($pointParser);
        $this->trackParser = $this->createMock(TrackParser::class);
        $this->reader = new GpxReader($this->trackParser);
    }

    public function testReadFromFile(): void
    {
        // Créer un fichier GPX temporaire pour le test
        $tmpFile = tempnam(sys_get_temp_dir(), 'gpx_test');
        $gpxContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<gpx version="1.1">
  <trk>
    <name>Test Track</name>
    <trkseg>
      <trkpt lat="48.8566" lon="2.3522">
        <ele>35</ele>
        <time>2023-01-01T10:00:00Z</time>
      </trkpt>
    </trkseg>
  </trk>
</gpx>
XML;
        file_put_contents($tmpFile, $gpxContent);

        // Mock des dépendances
        $track = $this->createMock(\PhpGpxParser\Models\Track::class);
        $this->trackParser->expects($this->once())
            ->method('parseTrack')
            ->willReturn($track);

        // Appel de la méthode à tester
        $gpxFile = $this->reader->readFromFile($tmpFile);

        // Vérifications
        $this->assertInstanceOf(GpxFile::class, $gpxFile);

        // Nettoyage
        unlink($tmpFile);
    }

    public function testReadFromFileNonExistent(): void
    {
        $this->expectException(GpxParserException::class);
        $this->expectExceptionMessage("Le fichier GPX n'existe pas");

        $this->reader->readFromFile('non_existent_file.gpx');
    }

    public function testReadFromString(): void
    {
        $gpxContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<gpx version="1.1">
  <trk>
    <name>Test Track</name>
    <trkseg>
      <trkpt lat="48.8566" lon="2.3522">
        <ele>35</ele>
        <time>2023-01-01T10:00:00Z</time>
      </trkpt>
    </trkseg>
  </trk>
</gpx>
XML;

        // Mock des dépendances
        $track = $this->createMock(\PhpGpxParser\Models\Track::class);
        $this->trackParser->expects($this->once())
            ->method('parseTrack')
            ->willReturn($track);

        // Appel de la méthode à tester
        $gpxFile = $this->reader->readFromString($gpxContent);

        // Vérifications
        $this->assertInstanceOf(GpxFile::class, $gpxFile);
    }

    public function testReadFromXml(): void
    {
        // Créer un SimpleXMLElement pour le test
        $gpxContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<gpx version="1.1">
  <trk>
    <name>Test Track</name>
    <trkseg>
      <trkpt lat="48.8566" lon="2.3522">
        <ele>35</ele>
        <time>2023-01-01T10:00:00Z</time>
      </trkpt>
    </trkseg>
  </trk>
</gpx>
XML;
        $xml = simplexml_load_string($gpxContent);

        // Mock des dépendances
        $track = $this->createMock(\PhpGpxParser\Models\Track::class);
        $this->trackParser->expects($this->once())
            ->method('parseTrack')
            ->willReturn($track);

        // Appel de la méthode à tester
        $gpxFile = $this->reader->readFromXml($xml);

        // Vérifications
        $this->assertInstanceOf(GpxFile::class, $gpxFile);
    }
}
