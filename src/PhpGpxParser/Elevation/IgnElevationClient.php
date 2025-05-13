<?php

namespace PhpGpxParser\Elevation;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class IgnElevationClient
{
    private const BASE_URL = 'https://data.geopf.fr/altimetrie/1.0/calcul/alti/rest/elevation.json';
    private const MAX_POINTS = 5000;

    public function __construct(
        private readonly HttpClientInterface $client,
    ) {}

    /**
     * Récupère les élévations pour un ensemble de coordonnées.
     *
     * @param array $coords Tableau de coordonnées [lat, lon]
     * @param string $resource Ressource d'élévation à utiliser
     * @return array Tableau des élévations
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
                'indent' => 'false'
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
