<?php

namespace PhpGpxParser\Utils;

use PhpGpxParser\Models\TrackPoint;

class ElevationCalculator
{
    private float $gain = 0;
    private float $loss = 0;
    private ?float $min = null;
    private ?float $max = null;

    public static float $thresholdElevation = 10;

    /**
     * @param TrackPoint[] $points
     */
    public function calculate(array $points): void
    {
        if (empty($points)) {
            return;
        }

        $lastEle = $points[0]->getElevation();
        $this->min = $this->max = $lastEle;

        foreach ($points as $pt) {
            $ele = $pt->getElevation();
            if ($ele === null || $lastEle === null) {
                continue;
            }

            $this->min = min($this->min, $ele);
            $this->max = max($this->max, $ele);

            $delta = $ele - $lastEle;
            if ($delta >= self::$thresholdElevation) {
                $this->gain += $delta;
                $lastEle = $ele;
            } elseif ($delta <= -self::$thresholdElevation) {
                $this->loss += abs($delta);
                $lastEle = $ele;
            }
        }
    }

    public function getGain(): float
    {
        return round($this->gain, 1);
    }

    public function getLoss(): float
    {
        return round($this->loss, 1);
    }

    public function getMin(): ?float
    {
        return $this->min;
    }

    public function getMax(): ?float
    {
        return $this->max;
    }
}
