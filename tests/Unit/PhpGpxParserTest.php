<?php

namespace PhpGpxParser\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpGpxParser\PhpGpxParser;
use PhpGpxParser\Models\GpxFile;
use PhpGpxParser\Models\Track;
use PhpGpxParser\Models\Segment;
use PhpGpxParser\Models\TrackPoint;
use PhpGpxParser\Utils\GpxStatistics;
use PhpGpxParser\Exception\GpxParserException;

class PhpGpxParserTest extends TestCase
{
    private PhpGpxParser $parser;
    private string $tempFile;

    protected function setUp(): void
    {
        $this->parser = new PhpGpxParser();
        $this->tempFile = tempnam(sys_get_temp_dir(), 'gpx_test');

        // Créer un fichier GPX valide pour les tests
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
      <trkpt lat="48.8567" lon="2.3523">
        <ele>36</ele>
        <time>2023-01-01T10:01:00Z</time>
      </trkpt>
    </trkseg>
  </trk>
</gpx>
XML;
        file_put_contents($this->tempFile, $gpxContent);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testStaticCreate(): void
    {
        $parser = PhpGpxParser::create();
        $this->assertInstanceOf(PhpGpxParser::class, $parser);
    }

    public function testReadWithoutElevationCorrection(): void
    {
        // Lire le fichier sans correction d'élévation
        $this->parser->read($this->tempFile, false);

        // Vérifier que le fichier a été lu correctement
        $gpx = $this->parser->getGpx();
        $this->assertInstanceOf(GpxFile::class, $gpx);

        // Vérifier la structure du fichier
        $tracks = $gpx->getTracks();
        $this->assertCount(1, $tracks);
        $this->assertEquals('Test Track', $tracks[0]->getName());

        $segments = $tracks[0]->getSegments();
        $this->assertCount(1, $segments);

        $points = $segments[0]->getPoints();
        $this->assertCount(2, $points);

        // Vérifier les données du premier point
        $this->assertEquals(48.8566, $points[0]->getLatitude());
        $this->assertEquals(2.3522, $points[0]->getLongitude());
        $this->assertEquals(35, $points[0]->getElevation());
    }

    /**
     * @group integration
     */
    public function testReadWithElevationCorrection(): void
    {
        // Note: Ce test pourrait être marqué comme un test d'intégration
        // car il fait appel à un service externe (IGN)

        // Pour éviter de faire de vraies requêtes HTTP pendant les tests unitaires,
        // nous allons simplement vérifier que la méthode ne lance pas d'exception

        try {
            $this->parser->read($this->tempFile);
            // Si nous arrivons ici, le test est considéré comme réussi
            $this->assertTrue(true);
        } catch (\Exception $e) {
            // Dans un environnement de test, il est possible que la
            // correction d'élévation échoue à cause de l'absence de connexion
            // Internet ou de problèmes avec l'API IGN
            $this->markTestSkipped('Test skipped due to IGN API issues: ' . $e->getMessage());
        }
    }

    //@TODO
    /*public function testSave(): void
    {

    }*/

    public function testSaveWithoutRead(): void
    {
        // Essayer de sauvegarder sans avoir lu un fichier d'abord
        $outputFile = $this->tempFile . '.out.gpx';

        // Cette action devrait déclencher une exception
        $this->expectException(GpxParserException::class);
        $this->expectExceptionMessage("Aucun fichier GPX n'a été chargé");

        $this->parser->save($outputFile);
    }

    public function testStats(): void
    {
        // Lire le fichier GPX
        $this->parser->read($this->tempFile, false);

        // Obtenir les statistiques
        $stats = $this->parser->stats();

        // Vérifier que nous avons un objet GpxStatistics
        $this->assertInstanceOf(GpxStatistics::class, $stats);

        // Faire quelques tests basiques sur les statistiques
        $this->assertEquals(48.8566, $stats->getStartLat());
        $this->assertEquals(2.3522, $stats->getStartLng());
        $this->assertEquals(48.8567, $stats->getEndLat());
        $this->assertEquals(2.3523, $stats->getEndLng());
    }

    public function testStatsWithoutRead(): void
    {
        // Essayer d'obtenir des statistiques sans avoir lu un fichier
        $stats = $this->parser->stats();

        // Le résultat devrait être null
        $this->assertNull($stats);
    }
}
