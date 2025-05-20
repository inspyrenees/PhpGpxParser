<?php

namespace PhpGpxParser\Elevation;

use PhpGpxParser\Models\GpxFile;
use PhpGpxParser\Models\TrackPoint;

class ElevationCorrector
{
    private readonly IgnElevationClient $ignClient;

    public function __construct(
    ) {
        $this->ignClient = new IgnElevationClient();
    }

    /**
     * Corrige les altitudes des points GPX en utilisant l'API IGN.
     * Gère les retours sous forme de simple float.
     *
     * @param GpxFile $gpx
     * @return void
     */
    public function applyTo(GpxFile $gpx): void
    {
        $points = $gpx->getAllTrackPoints();
        $coords = array_map(fn(TrackPoint $pt) => [$pt->getLatitude(), $pt->getLongitude()], $points);

        $data = $this->ignClient->fetchElevations($coords);

        foreach ($points as $i => $pt) {
            $raw = $data[$i] ?? null;

            if (!is_numeric($raw)) {
                continue;
            }

            $z = (float) $raw;
            if ($z <= -9999) {
                continue;
            }

            $pt->setElevation($z);
        }
    }
}
