<?php

namespace PhpGpxParser;

use PhpGpxParser\Elevation\ElevationCorrector;
use PhpGpxParser\Exception\GpxParserException;
use PhpGpxParser\Models\GpxFile;
use PhpGpxParser\Parser\PointParser;
use PhpGpxParser\Parser\SegmentParser;
use PhpGpxParser\Parser\TrackParser;
use PhpGpxParser\Parser\GpxReader;
use PhpGpxParser\Utils\GpxStatistics;
use PhpGpxParser\Utils\GpxSmoother;
use PhpGpxParser\Writer\GpxWriter;

class PhpGpxParser
{
    private ?GpxFile $gpx = null;
    private GpxReader $reader;
    private GpxWriter $writer;
    private ElevationCorrector $corrector;

    /**
     * Elevation threshold in meters used to filter insignificant elevation changes.
     *
     * @var int $thresholdElevation
     */
    public static int $thresholdElevation = 10;

    /**
     * Distance threshold in meters used to group nearby track points.
     *
     * @var int $thresholdDistance
     */
    public static int $thresholdDistance = 5;

    /**
     * Crée une nouvelle instance du parser GPX.
     */
    public function __construct()
    {
        // Initialisation des parsers
        $pointParser = new PointParser();
        $segmentParser = new SegmentParser($pointParser);
        $trackParser = new TrackParser($segmentParser);

        // Initialisation des services principaux
        $this->reader = new GpxReader($trackParser);
        $this->writer = new GpxWriter();

        $this->corrector = new ElevationCorrector();
    }

    /**
     * Crée une instance avec une configuration par défaut.
     *
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Traite le fichier GPX : lecture, correction d'altitude optionnelle.
     *
     * @param string $inputPath Chemin vers le fichier GPX source
     * @param bool $elevationCorrection Active la correction de l'élévation
     * @return self
     * @throws GpxParserException Si le fichier GPX est invalide ou inaccessible
     */
    public function read(string $inputPath, bool $elevationCorrection = true): self
    {
        $this->gpx = $this->reader->readFromFile($inputPath);

        if ($elevationCorrection) {
            $this->corrector->applyTo($this->gpx);
            $this->writer->write($this->gpx, $inputPath);
        }

        return $this;
    }

    /**
     * Applique le filtre Savitzky-Golay pour lisser les données d'élévation.
     *
     * @param int $windowSize Taille de la fenêtre (doit être impair)
     * @param int $polyOrder Ordre du polynôme (généralement 2 ou 3)
     * @return self
     * @throws GpxParserException Si aucun fichier GPX n'a été chargé
     */
    public function smoothElevation(int $windowSize = 9, int $polyOrder = 2): self
    {
        if ($this->gpx === null) {
            throw new GpxParserException("Aucun fichier GPX n'a été chargé. Appelez read() d'abord.");
        }

        $this->gpx = GpxSmoother::smoothElevation($this->gpx, $windowSize, $polyOrder);
        return $this;
    }

    /**
     * Applique le filtre Savitzky-Golay pour lisser les coordonnées du tracé.
     *
     * @param int $windowSize Taille de la fenêtre (doit être impair)
     * @param int $polyOrder Ordre du polynôme (généralement 2 ou 3)
     * @return self
     * @throws GpxParserException Si aucun fichier GPX n'a été chargé
     */
    public function smoothTrack(int $windowSize = 9, int $polyOrder = 2): self
    {
        if ($this->gpx === null) {
            throw new GpxParserException("Aucun fichier GPX n'a été chargé. Appelez read() d'abord.");
        }

        $this->gpx = GpxSmoother::smoothTrack($this->gpx, $windowSize, $polyOrder);
        return $this;
    }

    /**
     * Sauvegarde le fichier GPX à un nouvel emplacement.
     *
     * @param string $outputPath Chemin de sortie du fichier GPX
     * @return self
     * @throws GpxParserException Si aucun fichier GPX n'a été chargé
     */
    public function save(string $outputPath): self
    {
        if ($this->gpx === null) {
            throw new GpxParserException("Aucun fichier GPX n'a été chargé. Appelez read() d'abord.");
        }

        $this->writer->write($this->gpx, $outputPath);
        return $this;
    }

    /**
     * Calcule les statistiques du GPX en cours
     *
     * @return GpxStatistics|null Les statistiques calculées ou null si aucun GPX n'a été traité
     */
    public function stats(): ?GpxStatistics
    {
        if ($this->gpx === null) {
            return null;
        }

        return GpxStatistics::fromTrackPoints($this->gpx->getAllTrackPoints());
    }

    /**
     * Récupère le fichier GPX traité.
     *
     * @return GpxFile|null
     */
    public function getGpx(): ?GpxFile
    {
        return $this->gpx;
    }
}
