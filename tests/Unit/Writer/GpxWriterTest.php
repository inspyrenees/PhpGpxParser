<?php

namespace PhpGpxParser\Tests\Unit\Writer;

use PHPUnit\Framework\TestCase;
use PhpGpxParser\Writer\GpxWriter;
use PhpGpxParser\Models\GpxFile;
use PhpGpxParser\Models\Track;
use PhpGpxParser\Models\Segment;
use PhpGpxParser\Models\TrackPoint;

class GpxWriterTest extends TestCase
{
    private GpxWriter $writer;
    private string $tempFile;

    protected function setUp(): void
    {
        $this->writer = new GpxWriter();
        $this->tempFile = tempnam(sys_get_temp_dir(), 'gpx_test');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testWrite(): void
    {
        // Créer un fichier GPX en mémoire
        $gpxFile = new GpxFile();

        // Créer une piste avec un segment et des points
        $track = new Track();
        $track->setName('Test Track');

        $segment = new Segment();
        $segment->addPoint(new TrackPoint(48.8566, 2.3522, 35.0, new \DateTimeImmutable('2023-01-01T10:00:00Z')));
        $segment->addPoint(new TrackPoint(48.8567, 2.3523, 36.0, new \DateTimeImmutable('2023-01-01T10:01:00Z')));

        $track->addSegment($segment);
        $gpxFile->addTrack($track);

        // Écrire le fichier GPX
        $this->writer->write($gpxFile, $this->tempFile);

        // Vérifier que le fichier a été créé
        $this->assertFileExists($this->tempFile);

        // Lire le fichier et vérifier son contenu
        $content = file_get_contents($this->tempFile);
        $xml = simplexml_load_string($content);

        $this->assertNotFalse($xml);
        $this->assertEquals('1.1', $xml['version']);
        $this->assertStringContainsString('App', $xml['creator']);

        // Vérifier la structure du fichier
        $this->assertCount(1, $xml->trk);
        $this->assertEquals('Test Track', (string)$xml->trk->name);
        $this->assertCount(1, $xml->trk->trkseg);
        $this->assertCount(2, $xml->trk->trkseg->trkpt);

        // Vérifier le premier point
        $point1 = $xml->trk->trkseg->trkpt[0];
        $this->assertEquals('48.8566', (string)$point1['lat']);
        $this->assertEquals('2.3522', (string)$point1['lon']);
        $this->assertEquals('35', (string)$point1->ele);
        $this->assertEquals('2023-01-01T10:00:00+00:00', (string)$point1->time);

        // Vérifier le deuxième point
        $point2 = $xml->trk->trkseg->trkpt[1];
        $this->assertEquals('48.8567', (string)$point2['lat']);
        $this->assertEquals('2.3523', (string)$point2['lon']);
        $this->assertEquals('36', (string)$point2->ele);
        $this->assertEquals('2023-01-01T10:01:00+00:00', (string)$point2->time);
    }

    public function testWriteWithoutElevationAndTime(): void
    {
        // Créer un fichier GPX en mémoire
        $gpxFile = new GpxFile();

        // Créer une piste avec un segment et des points sans élévation ni temps
        $track = new Track();
        $track->setName('Test Track');

        $segment = new Segment();
        $segment->addPoint(new TrackPoint(48.8566, 2.3522));

        $track->addSegment($segment);
        $gpxFile->addTrack($track);

        // Écrire le fichier GPX
        $this->writer->write($gpxFile, $this->tempFile);

        // Vérifier que le fichier a été créé
        $this->assertFileExists($this->tempFile);

        // Lire le fichier et vérifier son contenu
        $content = file_get_contents($this->tempFile);
        $xml = simplexml_load_string($content);

        $this->assertNotFalse($xml);

        // Vérifier le premier point
        $point = $xml->trk->trkseg->trkpt[0];
        $this->assertEquals('48.8566', (string)$point['lat']);
        $this->assertEquals('2.3522', (string)$point['lon']);
        $this->assertFalse(isset($point->ele));
        $this->assertFalse(isset($point->time));
    }
}
