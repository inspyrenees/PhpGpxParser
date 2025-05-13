<?php

namespace PhpGpxParser\Tests\Unit\Utils;

use PhpGpxParser\Models\TrackPoint;
use PhpGpxParser\Utils\DistanceCalculator;
use PHPUnit\Framework\TestCase;

class DistanceCalculatorTest extends TestCase
{
    private DistanceCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new DistanceCalculator();
    }

    public function testCalculateEmptyPoints(): void
    {
        $this->calculator->calculate([]);
        $this->assertEquals(0.0, $this->calculator->getTotal());
    }

    public function testCalculateSinglePoint(): void
    {
        $point = new TrackPoint(45.5, 6.5);
        $this->calculator->calculate([$point]);
        $this->assertEquals(0.0, $this->calculator->getTotal());
    }

    public function testCalculateIdenticalPoints(): void
    {
        $points = [
            new TrackPoint(45.5, 6.5),
            new TrackPoint(45.5, 6.5),
        ];

        $this->calculator->calculate($points);
        $this->assertEquals(0.0, $this->calculator->getTotal());
    }

    public function testCalculateWithThreshold(): void
    {
        // Temporairement modifier la valeur seuil pour le test
        $originalThreshold = DistanceCalculator::$thresholdDistance;
        DistanceCalculator::$thresholdDistance = 1000; // 1km

        $points = [
            new TrackPoint(45.5, 6.5),
            new TrackPoint(45.51, 6.51), // Distance < 1km, devrait être ignorée
        ];

        $this->calculator->calculate($points);
        $this->assertEquals(1357.8, $this->calculator->getTotal());

        // Restaurer la valeur originale
        DistanceCalculator::$thresholdDistance = $originalThreshold;
    }

    public function testShouldCalculateDistanceBetweenPoints(): void
    {
        // Définir une valeur seuil pour le test
        $originalThreshold = DistanceCalculator::$thresholdDistance;
        DistanceCalculator::$thresholdDistance = 0; // Accepter toutes les distances

        $points = [
            new TrackPoint(45.0, 6.0),
            new TrackPoint(45.1, 6.0),  // ~ 11km au nord
        ];

        $this->calculator->calculate($points);
        // La distance devrait être d'environ 11km
        $this->assertGreaterThan(10000, $this->calculator->getTotal());
        $this->assertLessThan(12000, $this->calculator->getTotal());

        // Restaurer la valeur originale
        DistanceCalculator::$thresholdDistance = $originalThreshold;
    }

    public function testShouldAccumulateDistances(): void
    {
        // Définir une valeur seuil pour le test
        $originalThreshold = DistanceCalculator::$thresholdDistance;
        DistanceCalculator::$thresholdDistance = 0; // Accepter toutes les distances

        $points = [
            new TrackPoint(45.0, 6.0),
            new TrackPoint(45.1, 6.0),  // ~ 11km au nord
            new TrackPoint(45.1, 6.1),  // ~ 8km à l'est
        ];

        $this->calculator->calculate($points);
        // La distance totale devrait être d'environ 19km
        $this->assertGreaterThan(18000, $this->calculator->getTotal());
        $this->assertLessThan(20000, $this->calculator->getTotal());

        // Restaurer la valeur originale
        DistanceCalculator::$thresholdDistance = $originalThreshold;
    }
}
