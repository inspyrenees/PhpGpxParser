<?php

namespace PhpGpxParser\Models;

class GpxFile
{
    /** @var Track[] */
    private array $tracks = [];

    public function addTrack(Track $track): void
    {
        $this->tracks[] = $track;
    }

    /**
     * @return Track[]
     */
    public function getTracks(): array
    {
        return $this->tracks;
    }

    /**
     * Récupère tous les points de toutes les pistes et segments
     *
     * @return TrackPoint[]
     */
    public function getAllTrackPoints(): array
    {
        $allPoints = [];
        foreach ($this->tracks as $track) {
            foreach ($track->getSegments() as $segment) {
                $allPoints = array_merge($allPoints, $segment->getPoints());
            }
        }
        return $allPoints;
    }

    public function isEmpty(): bool
    {
        return empty($this->tracks);
    }
}
