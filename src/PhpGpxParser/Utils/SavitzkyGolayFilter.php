<?php

namespace PhpGpxParser\Utils;

use MathPHP\Exception\MatrixException;
use MathPHP\LinearAlgebra\MatrixFactory;

class SavitzkyGolayFilter
{
    /**
     * Cache des coefficients calculés pour éviter les recalculs
     *
     * @var array<string, array<float>>
     */
    private static array $coefficientsCache = [];

    /**
     * Nombre maximum d'entrées dans le cache
     */
    private const MAX_CACHE_SIZE = 50;

    /**
     * Applique un filtre Savitzky-Golay avec gestion améliorée des bords par réflexion.
     *
     * @param array<float> $data Les données à filtrer
     * @param int $windowSize La taille de la fenêtre (doit être impair et >= 3)
     * @param int $polyOrder L'ordre du polynôme (généralement 2 ou 3, doit être < windowSize)
     * @param string $mode Mode de gestion des bords: 'reflect' (par défaut), 'nearest', 'wrap'
     * @return array<float> Les données filtrées
     * @throws \InvalidArgumentException Si les paramètres ne sont pas valides
     */
    public static function filter(
        array $data,
        int $windowSize = 9,
        int $polyOrder = 2,
        string $mode = 'reflect'
    ): array {
        self::validateParameters($data, $windowSize, $polyOrder);

        $n = count($data);

        if ($n < $windowSize) {
            return $data;
        }

        $coeffs = self::getCoefficients($windowSize, $polyOrder);
        $halfWindow = intdiv($windowSize, 2);
        $extendedData = self::extendData($data, $halfWindow, $mode);

        return self::applyConvolution($extendedData, $coeffs, $halfWindow, $n);
    }

    /**
     * Valide les paramètres d'entrée
     *
     * @throws \InvalidArgumentException
     */
    private static function validateParameters(array $data, int $windowSize, int $polyOrder): void
    {
        if (empty($data)) {
            throw new \InvalidArgumentException("Le tableau de données ne peut pas être vide");
        }

        if ($windowSize < 3) {
            throw new \InvalidArgumentException(
                "La taille de la fenêtre doit être au minimum 3 (reçu: $windowSize)"
            );
        }

        if ($windowSize % 2 === 0) {
            throw new \InvalidArgumentException(
                "La taille de la fenêtre doit être impaire (reçu: $windowSize)"
            );
        }

        if ($polyOrder < 1) {
            throw new \InvalidArgumentException(
                "L'ordre du polynôme doit être au minimum 1 (reçu: $polyOrder)"
            );
        }

        if ($polyOrder >= $windowSize) {
            throw new \InvalidArgumentException(
                "L'ordre du polynôme ($polyOrder) doit être inférieur à la taille de la fenêtre ($windowSize)"
            );
        }

        foreach ($data as $i => $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException(
                    "Toutes les valeurs doivent être numériques (position $i: " . gettype($value) . ")"
                );
            }
        }
    }

    /**
     * Récupère les coefficients depuis le cache ou les calcule
     *
     * @return array<float>
     */
    private static function getCoefficients(int $windowSize, int $polyOrder): array
    {
        $cacheKey = "{$windowSize}_{$polyOrder}";

        if (!isset(self::$coefficientsCache[$cacheKey])) {
            if (count(self::$coefficientsCache) >= self::MAX_CACHE_SIZE) {
                array_shift(self::$coefficientsCache);
            }

            self::$coefficientsCache[$cacheKey] = self::calculateCoefficients($windowSize, $polyOrder);
        }

        return self::$coefficientsCache[$cacheKey];
    }

    /**
     * Calcule les coefficients du filtre Savitzky-Golay en utilisant MathPHP
     *
     * @return array<float>
     * @throws \RuntimeException Si le calcul échoue
     */
    private static function calculateCoefficients(int $windowSize, int $polyOrder): array
    {
        $halfWindow = intdiv($windowSize, 2);

        try {
            $vandermonde = [];
            for ($i = -$halfWindow; $i <= $halfWindow; $i++) {
                $row = [];
                for ($j = 0; $j <= $polyOrder; $j++) {
                    $row[] = pow($i, $j);
                }
                $vandermonde[] = $row;
            }

            $A = MatrixFactory::create($vandermonde);

            $AT = $A->transpose();
            $ATA = $AT->multiply($A);

            $det = $ATA->det();
            if (abs($det) < 1e-10) {
                throw new \RuntimeException(
                    "La matrice ATA est singulière (det=$det). " .
                    "Essayez de réduire l'ordre du polynôme ou d'augmenter la taille de la fenêtre."
                );
            }

            $ATAInv = $ATA->inverse();
            $pseudoInverse = $ATAInv->multiply($AT);

            $coefficients = [];
            for ($i = 0; $i < $windowSize; $i++) {
                $coefficients[] = $pseudoInverse->get(0, $i);
            }

            return $coefficients;

        } catch (MatrixException $e) {
            throw new \RuntimeException(
                "Erreur lors du calcul des coefficients Savitzky-Golay: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Étend les données aux bords selon le mode spécifié
     *
     * @param array<float> $data
     * @return array<float>
     */
    private static function extendData(array $data, int $halfWindow, string $mode): array
    {
        $n = count($data);
        $extendedData = [];

        switch ($mode) {
            case 'reflect':
                for ($i = $halfWindow; $i > 0; $i--) {
                    $extendedData[] = $data[$i];
                }
                $extendedData = array_merge($extendedData, $data);
                for ($i = $n - 2; $i >= $n - $halfWindow - 1; $i--) {
                    $extendedData[] = $data[$i];
                }
                break;

            case 'nearest':
                $extendedData = array_merge(
                    array_fill(0, $halfWindow, $data[0]),
                    $data,
                    array_fill(0, $halfWindow, $data[$n - 1])
                );
                break;

            case 'wrap':
                $extendedData = array_merge(
                    array_slice($data, -$halfWindow),
                    $data,
                    array_slice($data, 0, $halfWindow)
                );
                break;

            default:
                throw new \InvalidArgumentException(
                    "Mode de gestion des bords invalide: '$mode'. " .
                    "Utilisez 'reflect', 'nearest' ou 'wrap'."
                );
        }

        return $extendedData;
    }

    /**
     * Applique la convolution avec les coefficients
     *
     * @param array<float> $extendedData
     * @param array<float> $coeffs
     * @return array<float>
     */
    private static function applyConvolution(
        array $extendedData,
        array $coeffs,
        int $halfWindow,
        int $originalSize
    ): array {
        $result = [];
        $windowSize = count($coeffs);

        for ($i = $halfWindow; $i < $originalSize + $halfWindow; $i++) {
            $sum = 0.0;
            for ($j = 0; $j < $windowSize; $j++) {
                $sum += $coeffs[$j] * $extendedData[$i - $halfWindow + $j];
            }
            $result[] = $sum;
        }

        return $result;
    }

    /**
     * Vide le cache des coefficients (utile pour les tests ou pour libérer de la mémoire)
     */
    public static function clearCache(): void
    {
        self::$coefficientsCache = [];
    }

    /**
     * Applique le filtre avec dérivée (utile pour détecter les changements de pente)
     *
     * @param array<float> $data
     * @param int $windowSize
     * @param int $polyOrder
     * @param int $derivative Ordre de la dérivée (1 ou 2)
     * @param string $mode
     * @return array<float>
     */
    public static function filterWithDerivative(
        array $data,
        int $windowSize = 9,
        int $polyOrder = 2,
        int $derivative = 1,
        string $mode = 'reflect'
    ): array {
        self::validateParameters($data, $windowSize, $polyOrder);

        if ($derivative < 1 || $derivative > 2) {
            throw new \InvalidArgumentException(
                "L'ordre de la dérivée doit être 1 ou 2 (reçu: $derivative)"
            );
        }

        $n = count($data);
        if ($n < $windowSize) {
            return array_fill(0, $n, 0.0);
        }

        $coeffs = self::calculateDerivativeCoefficients($windowSize, $polyOrder, $derivative);
        $halfWindow = intdiv($windowSize, 2);
        $extendedData = self::extendData($data, $halfWindow, $mode);

        return self::applyConvolution($extendedData, $coeffs, $halfWindow, $n);
    }

    /**
     * Calcule les coefficients pour la dérivée
     *
     * @return array<float>
     */
    private static function calculateDerivativeCoefficients(
        int $windowSize,
        int $polyOrder,
        int $derivative
    ): array {
        $halfWindow = intdiv($windowSize, 2);

        try {
            $vandermonde = [];
            for ($i = -$halfWindow; $i <= $halfWindow; $i++) {
                $row = [];
                for ($j = 0; $j <= $polyOrder; $j++) {
                    $row[] = pow($i, $j);
                }
                $vandermonde[] = $row;
            }

            $A = MatrixFactory::create($vandermonde);
            $AT = $A->transpose();
            $ATA = $AT->multiply($A);
            $ATAInv = $ATA->inverse();
            $pseudoInverse = $ATAInv->multiply($AT);

            $coefficients = [];
            for ($i = 0; $i < $windowSize; $i++) {
                $coefficients[] = $pseudoInverse->get($derivative, $i);
            }

            return $coefficients;

        } catch (MatrixException $e) {
            throw new \RuntimeException(
                "Erreur lors du calcul des coefficients de dérivée: " . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
