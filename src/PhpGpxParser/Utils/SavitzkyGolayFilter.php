<?php

namespace PhpGpxParser\Utils;

class SavitzkyGolayFilter
{
    /**
     * Applique un filtre Savitzky-Golay avec gestion améliorée des bords par réflexion.
     *
     * @param array $data Les données à filtrer
     * @param int $windowSize La taille de la fenêtre (doit être impair)
     * @param int $polyOrder L'ordre du polynôme (généralement 2 ou 3)
     * @return array Les données filtrées
     * @throws \InvalidArgumentException Si les paramètres ne sont pas valides
     */
    public static function filter(array $data, int $windowSize = 9, int $polyOrder = 2): array
    {
        // Vérification des paramètres
        if ($windowSize % 2 === 0) {
            throw new \InvalidArgumentException("La taille de la fenêtre doit être impaire");
        }

        if ($polyOrder >= $windowSize) {
            throw new \InvalidArgumentException("L'ordre du polynôme doit être inférieur à la taille de la fenêtre");
        }

        $n = count($data);
        if ($n < $windowSize) {
            // Si nous avons trop peu de points, retourner les données originales
            return $data;
        }

        // Calculer les coefficients Savitzky-Golay
        $coeffs = self::calculateCoefficients($windowSize, $polyOrder);
        $halfWindow = intdiv($windowSize, 2);

        // Créer une série de données étendue avec réflexion aux bords
        $extendedData = [];

        // Réflexion au début - miroir des premiers points
        for ($i = $halfWindow - 1; $i >= 0; $i--) {
            $extendedData[] = $data[$i];
        }

        // Données originales
        foreach ($data as $value) {
            $extendedData[] = $value;
        }

        // Réflexion à la fin - miroir des derniers points
        for ($i = $n - 2; $i >= $n - $halfWindow - 1; $i--) {
            $extendedData[] = $data[$i];
        }

        // Appliquer le filtre sur les données étendues
        $result = [];
        for ($i = $halfWindow; $i < $n + $halfWindow; $i++) {
            $sum = 0;
            for ($j = -$halfWindow; $j <= $halfWindow; $j++) {
                $sum += $coeffs[$j + $halfWindow] * $extendedData[$i + $j];
            }
            $result[] = $sum;
        }

        return $result;
    }

    /**
     * Calcule les coefficients du filtre Savitzky-Golay
     *
     * @param int $windowSize Taille de la fenêtre
     * @param int $polyOrder Ordre du polynôme
     * @return array Coefficients du filtre
     */
    private static function calculateCoefficients(int $windowSize, int $polyOrder): array
    {
        $halfWindow = intdiv($windowSize, 2);

        // Créer la matrice de Vandermonde
        $A = [];
        for ($i = -$halfWindow; $i <= $halfWindow; $i++) {
            $row = [];
            for ($j = 0; $j <= $polyOrder; $j++) {
                $row[] = pow($i, $j);
            }
            $A[] = $row;
        }

        // Calculer la pseudo-inverse (A^T * A)^-1 * A^T
        $AT = self::transpose($A);
        $ATA = self::matrixMultiply($AT, $A);
        $ATAInv = self::pseudoInverse($ATA);
        $pseudoInv = self::matrixMultiply($ATAInv, $AT);

        // Les coefficients sont dans la première colonne de la pseudo-inverse
        $coeffs = [];
        for ($i = 0; $i < $windowSize; $i++) {
            $coeffs[] = $pseudoInv[0][$i];
        }

        return $coeffs;
    }

    /**
     * Transpose une matrice
     */
    private static function transpose(array $matrix): array
    {
        $rows = count($matrix);
        $cols = count($matrix[0]);

        $result = [];
        for ($j = 0; $j < $cols; $j++) {
            $result[$j] = [];
            for ($i = 0; $i < $rows; $i++) {
                $result[$j][$i] = $matrix[$i][$j];
            }
        }

        return $result;
    }

    /**
     * Multiplie deux matrices
     */
    private static function matrixMultiply(array $A, array $B): array
    {
        $rowsA = count($A);
        $colsA = count($A[0]);
        $rowsB = count($B);
        $colsB = count($B[0]);

        if ($colsA !== $rowsB) {
            throw new \InvalidArgumentException("Dimensions de matrices incompatibles pour la multiplication");
        }

        $result = [];
        for ($i = 0; $i < $rowsA; $i++) {
            $result[$i] = [];
            for ($j = 0; $j < $colsB; $j++) {
                $sum = 0;
                for ($k = 0; $k < $colsA; $k++) {
                    $sum += $A[$i][$k] * $B[$k][$j];
                }
                $result[$i][$j] = $sum;
            }
        }

        return $result;
    }

    /**
     * Calcule la pseudo-inverse d'une matrice carrée
     * Note: cette implémentation est simplifiée et peut ne pas fonctionner pour des matrices mal conditionnées
     */
    private static function pseudoInverse(array $matrix): array
    {
        $n = count($matrix);

        // Copier la matrice
        $A = [];
        for ($i = 0; $i < $n; $i++) {
            $A[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $A[$i][$j] = $matrix[$i][$j];
            }
        }

        // Créer la matrice identité
        $I = [];
        for ($i = 0; $i < $n; $i++) {
            $I[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $I[$i][$j] = ($i === $j) ? 1.0 : 0.0;
            }
        }

        // Élimination de Gauss-Jordan
        for ($i = 0; $i < $n; $i++) {
            // Trouver le pivot maximal
            $maxVal = abs($A[$i][$i]);
            $maxRow = $i;
            for ($k = $i + 1; $k < $n; $k++) {
                if (abs($A[$k][$i]) > $maxVal) {
                    $maxVal = abs($A[$k][$i]);
                    $maxRow = $k;
                }
            }

            // Échanger les lignes si nécessaire
            if ($maxRow !== $i) {
                for ($k = 0; $k < $n; $k++) {
                    $tmp = $A[$i][$k];
                    $A[$i][$k] = $A[$maxRow][$k];
                    $A[$maxRow][$k] = $tmp;

                    $tmp = $I[$i][$k];
                    $I[$i][$k] = $I[$maxRow][$k];
                    $I[$maxRow][$k] = $tmp;
                }
            }

            // Normaliser la ligne i
            $pivot = $A[$i][$i];
            if (abs($pivot) < 1e-10) {
                // Matrice singulière, utiliser une petite valeur
                $pivot = ($pivot < 0) ? -1e-10 : 1e-10;
            }

            for ($j = 0; $j < $n; $j++) {
                $A[$i][$j] /= $pivot;
                $I[$i][$j] /= $pivot;
            }

            // Éliminer les autres lignes
            for ($j = 0; $j < $n; $j++) {
                if ($j !== $i) {
                    $factor = $A[$j][$i];
                    for ($k = 0; $k < $n; $k++) {
                        $A[$j][$k] -= $factor * $A[$i][$k];
                        $I[$j][$k] -= $factor * $I[$i][$k];
                    }
                }
            }
        }

        return $I;
    }
}
