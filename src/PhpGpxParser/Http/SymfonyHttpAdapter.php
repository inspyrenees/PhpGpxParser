<?php

namespace PhpGpxParser\Http;

use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface as SymfonyResponseStreamInterface;

/**
 * Adaptateur pour le client HTTP de Symfony.
 * Cette classe sert de wrapper pour utiliser le client HTTP de Symfony
 * dans l'application de manière transparente.
 */
class SymfonyHttpAdapter implements SymfonyHttpClientInterface
{
    private SymfonyHttpClientInterface $client;

    /**
     * Crée un nouvel adaptateur pour le client HTTP de Symfony.
     *
     */
    public function __construct()
    {
        $this->client = SymfonyHttpClient::create();
    }

    /**
     * {@inheritdoc}
     */
    public function request(string $method, string $url, array $options = []): SymfonyResponseInterface
    {
        return $this->client->request($method, $url, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function stream($responses, float|null $timeout = null): SymfonyResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    /**
     * {@inheritdoc}
     */
    public function withOptions(array $options): static
    {
        $clone = clone $this;
        $clone->client = $this->client->withOptions($options);
        return $clone;
    }
}
