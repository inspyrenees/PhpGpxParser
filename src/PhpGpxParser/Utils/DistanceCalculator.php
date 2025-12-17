<?php

namespace PhpGpxParser\Utils;

use Location\Coordinate;
use Location\Distance\Vincenty;
use PhpGpxParser\Models\TrackPoint;
use PhpGpxParser\PhpGpxParser;

class DistanceCalculator
{
    private float $totalDistance = 0;
    /**
     * @param TrackPoint[] $points
     */
    public function calculate(array $points): void
    {
        if (count($points) < 2) {
            return;
        }

        $lastPt = $points[0];
        $lastCoord = new Coordinate($lastPt->getLatitude(), $lastPt->getLongitude());

        foreach ($points as $pt) {
            if ($pt->getLatitude() === $lastCoord->getLat() && $pt->getLongitude() === $lastCoord->getLng()) {
                continue;
            }

            $coord = new Coordinate($pt->getLatitude(), $pt->getLongitude());
            $dist = $lastCoord->getDistance($coord, new Vincenty());

            if (!is_numeric($dist) || $dist < PhpGpxParser::$thresholdDistance) {
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
