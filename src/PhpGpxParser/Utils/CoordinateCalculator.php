<?php

namespace PhpGpxParser\Utils;

use PhpGpxParser\Models\TrackPoint;

class CoordinateCalculator
{
    private float $startLat;
    private float $startLng;
    private float $endLat;
    private float $endLng;
    private array $min = [null, null];
    private array $max = [null, null];

    /**
     * Compute start/end coordinates and bounding box (min/max lat/lon).
     *
     * @param TrackPoint[] $points
     */
    public function calculate(array $points): void
    {
        if (empty($points)) {
            return;
        }

        $first = reset($points);
        $last  = end($points);
        $this->startLat = $first->getLatitude();
        $this->startLng = $first->getLongitude();
        $this->endLat   = $last->getLatitude();
        $this->endLng   = $last->getLongitude();

        $minLat = $maxLat = $first->getLatitude();
        $minLon = $maxLon = $first->getLongitude();

        foreach ($points as $pt) {
            $minLat = min($minLat, $pt->getLatitude());
            $maxLat = max($maxLat, $pt->getLatitude());
            $minLon = min($minLon, $pt->getLongitude());
            $maxLon = max($maxLon, $pt->getLongitude());
        }

        $this->min = [$minLat, $minLon];
        $this->max = [$maxLat, $maxLon];
    }

    public function getStartLat(): float
    {
        return $this->startLat;
    }

    public function getStartLng(): float
    {
        return $this->startLng;
    }

    public function getEndLat(): float
    {
        return $this->endLat;
    }

    public function getEndLng(): float
    {
        return $this->endLng;
    }

    public function getMin(): array
    {
        return $this->min;
    }

    public function getMax(): array
    {
        return $this->max;
    }
}
