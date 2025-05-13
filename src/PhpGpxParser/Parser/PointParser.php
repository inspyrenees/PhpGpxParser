<?php

namespace PhpGpxParser\Parser;

use PhpGpxParser\Models\TrackPoint;

class PointParser
{
    /**
     * Parse un point de trace GPX et retourne un TrackPoint.
     *
     * @param \SimpleXMLElement $trkpt Élément <trkpt>
     * @return TrackPoint
     */
    public function parsePoint(\SimpleXMLElement $trkpt): TrackPoint
    {
        $lat = (float) $trkpt['lat'];
        $lon = (float) $trkpt['lon'];
        $elev = isset($trkpt->ele) ? (float) $trkpt->ele : null;
        $time = isset($trkpt->time) ? new \DateTimeImmutable((string) $trkpt->time) : null;

        return new TrackPoint($lat, $lon, $elev, $time);
    }
}
