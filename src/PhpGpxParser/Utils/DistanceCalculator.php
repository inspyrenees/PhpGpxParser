<?php

namespace PhpGpxParser\Utils;

use PhpGpxParser\Models\TrackPoint;
use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Distance\Distance;

class DistanceCalculator
{
    private float $totalDistance = 0;
    public static float $thresholdDistance = 5;

    /**
     * @param TrackPoint[] $points
     */
    public function calculate(array $points): void
    {
        if (count($points) < 2) {
            return;
        }

        $distance = new Distance();
        $lastPt = $points[0];
        $lastCoord = new Coordinate([$lastPt->getLatitude(), $lastPt->getLongitude()]);

        foreach ($points as $pt) {
            if ($pt->getLatitude() === $lastCoord->getLatitude() && $pt->getLongitude() === $lastCoord->getLongitude()) {
                continue;
            }

            $coord = new Coordinate([$pt->getLatitude(), $pt->getLongitude()]);
            $dist = $distance
                ->setFrom($lastCoord)
                ->setTo($coord)
                ->in('m')
                ->haversine();

            if (!is_numeric($dist) || $dist < self::$thresholdDistance) {
                continue;
            }

            $this->totalDistance += $dist;
            $lastCoord = $coord;
        }
    }

    public function getTotal(): float
    {
        return round($this->totalDistance, 1);
    }
}
