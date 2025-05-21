<?php

namespace PhpGpxParser\Utils;

class GpxStatistics
{
    private ElevationCalculator $elevation;
    private DistanceCalculator  $distance;
    private TimeCalculator      $time;
    private CoordinateCalculator $coordinate;

    public function __construct(
        ElevationCalculator  $elevation,
        DistanceCalculator   $distance,
        TimeCalculator       $time,
        CoordinateCalculator $coordinate
    ) {
        $this->elevation  = $elevation;
        $this->distance   = $distance;
        $this->time       = $time;
        $this->coordinate = $coordinate;
    }

    public static function fromTrackPoints(array $points): self
    {
        $e = new ElevationCalculator();
        $e->calculate($points);

        $d = new DistanceCalculator();
        $d->calculate($points);

        $t = new TimeCalculator();
        $t->calculate($points);

        $c = new CoordinateCalculator();
        $c->calculate($points);

        return new self($e, $d, $t, $c);
    }

    public function getElevationGain(): float
    {
        return $this->elevation->getGain();
    }

    public function getElevationLoss(): float
    {
        return $this->elevation->getLoss();
    }

    public function getMinElevation(): ?float
    {
        return $this->elevation->getMin();
    }

    public function getMaxElevation(): ?float
    {
        return $this->elevation->getMax();
    }

    public function getTotalDistance(): float
    {
        return $this->distance->getTotal();
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->time->getStartTime();
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->time->getEndTime();
    }

    public function getDuration(): int
    {
        return $this->time->getDuration();
    }

    public function getMovingTime(): int
    {
        return $this->time->getMovingTime();
    }

    public function getStoppedTime(): int
    {
        return $this->time->getStoppedTime();
    }

    public function getStartLat(): float
    {
        return $this->coordinate->getStartLat();
    }

    public function getStartLng(): float
    {
        return $this->coordinate->getStartLng();
    }

    public function getEndLat(): float
    {
        return $this->coordinate->getEndLat();
    }

    public function getEndLng(): float
    {
        return $this->coordinate->getEndLng();
    }

    public function getMinCoordinates(): array
    {
        return $this->coordinate->getMin();
    }

    public function getMaxCoordinates(): array
    {
        return $this->coordinate->getMax();
    }
}
