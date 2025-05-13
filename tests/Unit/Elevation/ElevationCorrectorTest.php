<?php

namespace PhpGpxParser\Tests\Unit\Elevation;

use PHPUnit\Framework\TestCase;
use PhpGpxParser\Elevation\ElevationCorrector;
use PhpGpxParser\Elevation\IgnElevationClient;
use PhpGpxParser\Models\GpxFile;
use PhpGpxParser\Models\Track;
use PhpGpxParser\Models\Segment;
use PhpGpxParser\Models\TrackPoint;

class ElevationCorrectorTest extends TestCase
{
    private ElevationCorrector $corrector;
    private IgnElevationClient $ignClient;

    protected function setUp(): void
    {
        $this->ignClient = $this->createMock(IgnElevationClient::class);
        $this->corrector = new ElevationCorrector($this->ignClient);
    }

    public function testApplyToWithValidElevations(): void
    {
        // Créer un fichier GPX avec des points
        $gpxFile = new GpxFile();
        $track = new Track();
        $segment = new Segment();

        $point1 = new TrackPoint(48.8566, 2.3522, 10.0);
        $point2 = new TrackPoint(48.8567, 2.3523, 15.0);
        $point3 = new TrackPoint(48.8568, 2.3524, 20.0);

        $segment->addPoint($point1);
        $segment->addPoint($point2);
        $segment->addPoint($point3);

        $track->addSegment($segment);
        $gpxFile->addTrack($track);

        // Configurer le mock IgnElevationClient pour retourner des élévations
        $this->ignClient->expects($this->once())
            ->method('fetchElevations')
            ->with([
                [48.8566, 2.3522],
                [48.8567, 2.3523],
                [48.8568, 2.3524]
            ])
            ->willReturn([42.5, 43.2, 44.8]);

        // Appel de la méthode à tester
        $this->corrector->applyTo($gpxFile);

        // Vérifier que les élévations ont été mises à jour
        $points = $gpxFile->getAllTrackPoints();
        $this->assertEquals(42.5, $points[0]->getElevation());
        $this->assertEquals(43.2, $points[1]->getElevation());
        $this->assertEquals(44.8, $points[2]->getElevation());
    }

    public function testApplyToWithInvalidElevations(): void
    {
        // Créer un fichier GPX avec des points
        $gpxFile = new GpxFile();
        $track = new Track();
        $segment = new Segment();

        $point1 = new TrackPoint(48.8566, 2.3522, 10.0);
        $point2 = new TrackPoint(48.8567, 2.3523, 15.0);

        $segment->addPoint($point1);
        $segment->addPoint($point2);

        $track->addSegment($segment);
        $gpxFile->addTrack($track);

        // Configurer le mock pour retourner une élévation invalide (-9999)
        // et une non-numérique
        $this->ignClient->expects($this->once())
            ->method('fetchElevations')
            ->willReturn([-9999, "N/A"]);

        // Appel de la méthode à tester
        $this->corrector->applyTo($gpxFile);

        // Vérifier que les élévations originales sont conservées
        $points = $gpxFile->getAllTrackPoints();
        $this->assertEquals(10.0, $points[0]->getElevation());
        $this->assertEquals(15.0, $points[1]->getElevation());
    }

    public function testApplyToWithMissingResponseValues(): void
    {
        // Créer un fichier GPX avec des points
        $gpxFile = new GpxFile();
        $track = new Track();
        $segment = new Segment();

        $point1 = new TrackPoint(48.8566, 2.3522, 10.0);
        $point2 = new TrackPoint(48.8567, 2.3523, 15.0);

        $segment->addPoint($point1);
        $segment->addPoint($point2);

        $track->addSegment($segment);
        $gpxFile->addTrack($track);

        // Configurer le mock pour retourner un tableau incomplet
        $this->ignClient->expects($this->once())
            ->method('fetchElevations')
            ->willReturn([42.0]); // Seulement une valeur pour deux points

        // Appel de la méthode à tester
        $this->corrector->applyTo($gpxFile);

        // Vérifier que la première élévation est mise à jour et la seconde est conservée
        $points = $gpxFile->getAllTrackPoints();
        $this->assertEquals(42.0, $points[0]->getElevation());
        $this->assertEquals(15.0, $points[1]->getElevation());
    }

    public function testApplyToEmptyGpxFile(): void
    {
        // Créer un fichier GPX vide
        $gpxFile = new GpxFile();

        // Le client IGN ne devrait pas être appelé
        $this->ignClient->expects($this->once())
            ->method('fetchElevations')
            ->with([])
            ->willReturn([]);

        // Appel de la méthode à tester
        $this->corrector->applyTo($gpxFile);

        // Rien à vérifier spécifiquement, le test réussit si aucune exception n'est levée
        $this->assertTrue(true);
    }
}
