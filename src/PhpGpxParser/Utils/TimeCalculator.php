<?php

namespace PhpGpxParser\Utils;

use PhpGpxParser\Models\TrackPoint;
use DateTimeInterface;

class TimeCalculator
{
    private ?DateTimeInterface $start = null;
    private ?DateTimeInterface $end = null;
    private int $duration = 0; // seconds

    /**
     * Determine start/end time and duration from ordered TrackPoints.
     *
     * @param TrackPoint[] $points
     */
    public function calculate(array $points): void
    {
        if (empty($points)) {
            return;
        }
        $first = reset($points);
        $last = end($points);

        $this->start = $first->getTime();
        $this->end = $last->getTime();

        if ($this->start instanceof DateTimeInterface && $this->end instanceof DateTimeInterface) {
            $this->duration = $this->end->getTimestamp() - $this->start->getTimestamp();
        }
    }

    public function getStart(): ?DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): ?DateTimeInterface
    {
        return $this->end;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }
}
