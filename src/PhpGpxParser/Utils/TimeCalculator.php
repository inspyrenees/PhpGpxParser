<?php

namespace PhpGpxParser\Utils;

use PhpGpxParser\Models\TrackPoint;
use DateTimeInterface;
use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Distance\Distance;

class TimeCalculator
{
    private ?DateTimeInterface $startTime = null;
    private ?DateTimeInterface $endTime = null;
    private int $duration = 0; // seconds
    private int $movingTime = 0; // seconds
    private int $stoppedTime = 0; // seconds

    /**
     * Determine start/end time and duration from ordered TrackPoints.
     * Also calculates moving time versus stopped time based on speed threshold.
     *
     * @param TrackPoint[] $points
     * @param float $speedThreshold Speed threshold in km/h below which is considered stopped
     */
    public function calculate(
        array $points,
        float $speedThreshold = 0.5
    ): void {
        if (empty($points)) {
            return;
        }

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

        $this->calculateMovingTime($points, $speedThreshold);
    }

    /**
     * Calculate the moving time by examining each segment between consecutive points
     *
     * @param TrackPoint[] $points
     * @param float $speedThreshold
     */
    private function calculateMovingTime(
        array $points,
        float $speedThreshold
    ): void {
        // Réinitialiser les compteurs
        $this->movingTime = 0;
        $this->stoppedTime = 0;

        if (count($points) < 2) {
            $this->movingTime = $this->duration;
            return;
        }

        $distance = new Distance();

        // Pour le premier point, nous n'avons pas de segment précédent, donc on l'ignore
        $lastPt = $points[0];
        $lastCoord = new Coordinate([$lastPt->getLatitude(), $lastPt->getLongitude()]);
        $lastTime = $lastPt->getTime();

        // Parcourir tous les points sauf le premier
        for ($i = 1; $i < count($points); $i++) {
            $point = $points[$i];

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
                // On calcule la vitesse uniquement si le segment a une durée positive
                $speed = $this->calculateSpeed($dist, $deltaTime);

                // On considère qu'on est en mouvement si la vitesse est supérieure au seuil
                if ($speed > $speedThreshold) {
                    $this->movingTime += $deltaTime;
                } else {
                    $this->stoppedTime += $deltaTime;
                }
            }

            // Mettre à jour les dernières valeurs pour le prochain segment
            $lastCoord = $coord;
            $lastTime = $point->getTime();
        }

        // Vérification finale : s'assurer que movingTime + stoppedTime = duration
        $calculatedTotal = $this->movingTime + $this->stoppedTime;
        if ($calculatedTotal != $this->duration) {
            // Ajuster le temps de mouvement pour qu'il corresponde à la durée totale
            $this->movingTime += ($this->duration - $calculatedTotal);
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
