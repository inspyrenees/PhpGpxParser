<?php

namespace PhpGpxParser\Elevation;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IgnElevationClient
{
    private const BASE_URL = 'https://data.geopf.fr/altimetrie/calcul/alti/rest/elevationLine.json';

    private const MAX_POINTS = 5000;

    private HttpClientInterface $client;

    public function __construct()
    {
        $this->client = HttpClient::create();
    }

    /**
     * Récupère les élévations pour un ensemble de coordonnées.
     *
     * @param array<array{0: float, 1: float}> $coords Tableau de coordonnées [lat, lon]
     * @param string $resource Ressource d'élévation à utiliser
     * @return array<float|null> Tableau des élévations (null en cas d'erreur pour un point)
     * @throws TransportExceptionInterface En cas d'erreur de connexion
     * @throws ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface En cas d'erreur HTTP
     */
    public function fetchElevations(array $coords, string $resource = 'ign_rge_alti_par_territoires'): array
    {
        if (empty($coords)) {
            return [];
        }

        $results = [];

        foreach (array_chunk($coords, self::MAX_POINTS) as $chunk) {
            $lats = implode('|', array_map(fn($c) => $c[0], $chunk));
            $lons = implode('|', array_map(fn($c) => $c[1], $chunk));

            $body = [
                'lat' => $lats,
                'lon' => $lons,
                'resource' => $resource,
                'delimiter' => '|',
                'zonly' => 'true',
                'measures' => 'false',
                'indent' => 'false',
                'profile_mode' => 'accurate',
                'sampling' => count($chunk)
            ];

            $response = $this->client->request('POST', self::BASE_URL, [
                'json' => $body,
            ]);

            $data = $response->toArray(false);
            $results = array_merge($results, $data['elevations'] ?? []);
        }

        return $results;
    }
}
