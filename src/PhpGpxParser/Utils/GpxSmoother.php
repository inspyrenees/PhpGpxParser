<?php

namespace PhpGpxParser\Utils;

use PhpGpxParser\Models\GpxFile;
use PhpGpxParser\Models\TrackPoint;

class GpxSmoother
{
    /**
     * Lisse les données d'élévation en utilisant le filtre Savitzky-Golay
     *
     * @param GpxFile $gpx Le fichier GPX à traiter
     * @param int $windowSize La taille de la fenêtre (doit être impair)
     * @param int $polyOrder L'ordre du polynôme (généralement 2 ou 3)
     * @return GpxFile Le fichier GPX avec les données lissées
     */
    public static function smoothElevation(GpxFile $gpx, int $windowSize = 9, int $polyOrder = 2): GpxFile
    {
        foreach ($gpx->getTracks() as $track) {
            foreach ($track->getSegments() as $segment) {
                $points = $segment->getPoints();

                // Extraire les élévations
                $elevations = array_map(function(TrackPoint $point) {
                    return $point->getElevation() ?? 0;
                }, $points);

                // Ne filtrer que si nous avons des élévations valides
                if (count(array_filter($elevations, fn($e) => $e !== 0)) > 0) {
                    // Appliquer le filtre Savitzky-Golay
                    $smoothedElevations = SavitzkyGolayFilter::filter($elevations, $windowSize, $polyOrder);

                    // Appliquer les élévations lissées
                    foreach ($points as $i => $point) {
                        if ($point->getElevation() !== null) {
                            $point->setElevation($smoothedElevations[$i]);
                        }
                    }
                }
            }
        }

        return $gpx;
    }

    /**
     * Lisse les données spatiales (latitude/longitude) en utilisant le filtre Savitzky-Golay
     * Utile pour lisser le tracé sur la carte.
     *
     * @param GpxFile $gpx Le fichier GPX à traiter
     * @param int $windowSize La taille de la fenêtre (doit être impair)
     * @param int $polyOrder L'ordre du polynôme (généralement 2 ou 3)
     * @return GpxFile Le fichier GPX avec les données lissées
     */
    public static function smoothTrack(GpxFile $gpx, int $windowSize = 9, int $polyOrder = 2): GpxFile
    {
        foreach ($gpx->getTracks() as $track) {
            foreach ($track->getSegments() as $segment) {
                $points = $segment->getPoints();

                // Extraire les latitudes et longitudes
                $latitudes = array_map(function(TrackPoint $point) {
                    return $point->getLatitude();
                }, $points);

                $longitudes = array_map(function(TrackPoint $point) {
                    return $point->getLongitude();
                }, $points);

                // Appliquer le filtre Savitzky-Golay
                $smoothedLatitudes = SavitzkyGolayFilter::filter($latitudes, $windowSize, $polyOrder);
                $smoothedLongitudes = SavitzkyGolayFilter::filter($longitudes, $windowSize, $polyOrder);

                // Appliquer les coordonnées lissées
                foreach ($points as $i => $point) {
                    $point->setLatitude($smoothedLatitudes[$i]);
                    $point->setLongitude($smoothedLongitudes[$i]);
                }
            }
        }

        return $gpx;
    }
}
