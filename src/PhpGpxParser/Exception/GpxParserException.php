<?php

namespace PhpGpxParser\Exception;

/**
 * Exception de base pour toutes les exceptions de la bibliothèque PhpGpxParser.
 *
 * Cette classe sert de classe parent pour toutes les exceptions spécifiques
 * à PhpGpxParser, permettant de les attraper facilement avec un seul bloc try/catch
 * si nécessaire.
 */
class GpxParserException extends \Exception
{
    /**
     * Construit une nouvelle exception PhpGpxParser.
     *
     * @param string $message Message décrivant l'erreur
     * @param int $code Code d'erreur optionnel
     * @param \Throwable|null $previous Exception précédente si imbrication d'exceptions
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Représentation sous forme de chaîne de caractères de l'exception.
     *
     * @return string
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
