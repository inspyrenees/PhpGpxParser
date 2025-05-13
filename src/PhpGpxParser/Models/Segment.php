<?php

namespace PhpGpxParser\Models;

class Segment
{
    /** @var TrackPoint[] */
    private array $points = [];

    public function addPoint(TrackPoint $point): void
    {
        $this->points[] = $point;
    }

    /**
     * @return TrackPoint[]
     */
    public function getPoints(): array
    {
        return $this->points;
    }

    public function isEmpty(): bool
    {
        return empty($this->points);
    }
}
