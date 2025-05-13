<?php

namespace PhpGpxParser\Utils;

use PhpGpxParser\Models\TrackPoint;
use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Distance\Distance;

class SpeedCalculator
{
    private float $totalTime = 0; // seconds
    private float $maxSpeed = 0; // km/h
    private float $avgSpeed = 0; // km/h

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
        $lastTime = $lastPt->getTime();

        $totalDist = 0;
        $timeSegments = [];

        foreach ($points as $pt) {
            if (!($lastTime instanceof \DateTimeInterface && $pt->getTime() instanceof \DateTimeInterface)) {
                continue;
            }

            if ($pt->getLatitude() === $lastCoord->getLatitude() && $pt->getLongitude() === $lastCoord->getLongitude()) {
                continue;
            }

            $coord = new Coordinate([$pt->getLatitude(), $pt->getLongitude()]);
            $dist = $distance
                ->setFrom($lastCoord)
                ->setTo($coord)
                ->in('m')
                ->haversine();

            $dt = $pt->getTime()->getTimestamp() - $lastTime->getTimestamp();

            if ($dist > 0 && $dt > 0) {
                $speed = ($dist / 1000) / ($dt / 3600);
                $this->maxSpeed = max($this->maxSpeed, $speed);
                $totalDist += $dist;
                $this->totalTime += $dt;
            }

            $lastCoord = $coord;
            $lastTime = $pt->getTime();
        }

        if ($this->totalTime > 0) {
            $this->avgSpeed = ($totalDist / 1000) / ($this->totalTime / 3600);
        }
    }

    public function getMax(): float
    {
        return round($this->maxSpeed, 1);
    }

    public function getAvg(): float
    {
        return round($this->avgSpeed, 1);
    }
}
