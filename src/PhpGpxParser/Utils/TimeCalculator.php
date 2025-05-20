<?php

namespace PhpGpxParser\Utils;

use PhpGpxParser\Models\TrackPoint;
use DateTimeInterface;
use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Distance\Distance;
use PhpGpxParser\PhpGpxParser;

class TimeCalculator
{
    private ?DateTimeInterface $startTime = null;
    private ?DateTimeInterface $endTime = null;
    private int $duration = 0; // seconds
    private int $movingTime = 0; // seconds
    private int $stoppedTime = 0; // seconds

    /**
     * Minimum duration in seconds to consider a stop as a real stop
     * For hiking, brief pauses under this threshold will be counted as moving time
     */
    private int $minStopDuration = 30;

    /**
     * Determine start/end time and duration from ordered TrackPoints.
     * Also calculates moving time versus stopped time based on speed threshold.
     * Enhanced for hiking activities with elevation consideration.
     *
     * @param TrackPoint[] $points
     * @param float $speedThreshold Speed threshold in km/h below which is considered stopped
     * @param float $distanceThreshold Distance threshold in meters below which movement is ignored
     * @param int $minStopDuration Minimum duration in seconds to consider a real stop
     */
    public function calculate(
        array $points,
        float $speedThreshold = 0.5,
        float $distanceThreshold = null,
        int $minStopDuration = 30
    ): void {
        if (empty($points)) {
            return;
        }

        if ($distanceThreshold === null) {
            $distanceThreshold = PhpGpxParser::$thresholdDistance;
        }

        $this->minStopDuration = $minStopDuration;

        // Determine start and end times
        $first = reset($points);
        $last = end($points);

        $this->startTime = $first->getTime();
        $this->endTime = $last->getTime();

        if ($this->startTime instanceof DateTimeInterface && $this->endTime instanceof DateTimeInterface) {
            $this->duration = $this->endTime->getTimestamp() - $this->startTime->getTimestamp();
        }

        if (count($points) < 2) {
            $this->movingTime = $this->duration;
            return;
        }

        $this->calculateMovingTime($points, $speedThreshold, $distanceThreshold);
    }

    /**
     * Calculate the moving time by examining each segment between consecutive points
     *
     * @param TrackPoint[] $points
     * @param float $speedThreshold
     * @param float $distanceThreshold
     */
    private function calculateMovingTime(
        array $points,
        float $speedThreshold,
        float $distanceThreshold
    ): void {
        $distance = new Distance();
        $lastPt = $points[0];
        $lastCoord = new Coordinate([$lastPt->getLatitude(), $lastPt->getLongitude()]);
        $lastTime = $lastPt->getTime();
        $lastElevation = $lastPt->getElevation();

        $potentialStopStart = null;
        $segmentStoppedTime = 0;

        foreach ($points as $i => $point) {
            if (!($lastTime instanceof \DateTimeInterface && $point->getTime() instanceof \DateTimeInterface)) {
                continue;
            }

            $coord = new Coordinate([$point->getLatitude(), $point->getLongitude()]);
            $dist = $distance
                ->setFrom($lastCoord)
                ->setTo($coord)
                ->in('m')
                ->haversine();

            $deltaTime = $point->getTime()->getTimestamp() - $lastTime->getTimestamp();

            if ($deltaTime > 0) {
                $speed = $this->calculateSpeed($dist, $deltaTime);
                $adjustedSpeedThreshold = $this->adjustSpeedThresholdByElevation(
                    $speedThreshold,
                    $lastElevation,
                    $point->getElevation(),
                    $dist
                );

                // Check if we're moving or stopped
                if ($speed > $adjustedSpeedThreshold && $dist >= $distanceThreshold) {
                    // We're moving
                    $this->movingTime += $deltaTime;

                    // If we were in a potential stop, but it was too short, add it back to moving time
                    if ($potentialStopStart !== null) {
                        if ($segmentStoppedTime < $this->minStopDuration) {
                            $this->movingTime += $segmentStoppedTime;
                            $this->stoppedTime -= $segmentStoppedTime;
                        }
                        $potentialStopStart = null;
                        $segmentStoppedTime = 0;
                    }
                } else {
                    // We're potentially stopped
                    $this->stoppedTime += $deltaTime;

                    // Track potential stop segments
                    if ($potentialStopStart === null) {
                        $potentialStopStart = $point->getTime();
                        $segmentStoppedTime = $deltaTime;
                    } else {
                        $segmentStoppedTime += $deltaTime;
                    }
                }
            }

            $lastCoord = $coord;
            $lastTime = $point->getTime();
            $lastElevation = $point->getElevation();
        }
    }

    /**
     * Calculate speed in km/h between two points
     *
     * @param float $distanceInMeters
     * @param int $timeInSeconds
     * @return float Speed in km/h
     */
    private function calculateSpeed(float $distanceInMeters, int $timeInSeconds): float
    {
        if ($distanceInMeters <= 0 || $timeInSeconds <= 0) {
            return 0;
        }

        return ($distanceInMeters / 1000) / ($timeInSeconds / 3600);
    }

    /**
     * Adjust speed threshold based on elevation change
     * Uphill hiking requires more effort, so we reduce the threshold
     *
     * @param float $baseThreshold Base speed threshold in km/h
     * @param float|null $lastElevation
     * @param float|null $currentElevation
     * @param float $distance Distance in meters
     * @return float Adjusted speed threshold
     */
    private function adjustSpeedThresholdByElevation(
        float $baseThreshold,
        ?float $lastElevation,
        ?float $currentElevation,
        float $distance
    ): float {
        if ($lastElevation === null || $currentElevation === null || $distance <= 0) {
            return $baseThreshold;
        }

        $elevChange = $currentElevation - $lastElevation;
        if ($elevChange > 0) {
            // Calculate grade as a percentage
            $grade = ($elevChange / $distance) * 100;

            // Reduce speed threshold on steep climbs (>15% grade)
            if ($grade > 15) {
                return $baseThreshold * 0.7;
            }
        }

        return $baseThreshold;
    }

    public function getStartTime(): ?DateTimeInterface
    {
        return $this->startTime;
    }

    public function getEndTime(): ?DateTimeInterface
    {
        return $this->endTime;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getMovingTime(): int
    {
        return $this->movingTime;
    }

    public function getStoppedTime(): int
    {
        return $this->stoppedTime;
    }
}
