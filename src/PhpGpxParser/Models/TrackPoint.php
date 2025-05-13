<?php

namespace PhpGpxParser\Models;

class TrackPoint
{
    public function __construct(
        private float $latitude,
        private float $longitude,
        private ?float $elevation = null,
        private ?\DateTimeImmutable $time = null,
    ) {}

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getElevation(): ?float
    {
        return $this->elevation;
    }

    /**
     * Modifie l'élévation du point
     *
     * @param float|null $elevation Nouvelle élévation
     * @return void
     */
    public function setElevation(?float $elevation): void
    {
        $this->elevation = $elevation;
    }

    public function getTime(): ?\DateTimeImmutable
    {
        return $this->time;
    }
}
