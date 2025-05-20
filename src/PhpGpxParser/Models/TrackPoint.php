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

    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getElevation(): ?float
    {
        return $this->elevation;
    }

    public function setElevation(float $elevation): void
    {
        $this->elevation = $elevation;
    }

    public function getTime(): ?\DateTimeImmutable
    {
        return $this->time;
    }
}
