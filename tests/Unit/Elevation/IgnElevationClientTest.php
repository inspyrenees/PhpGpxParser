<?php

namespace PhpGpxParser\Tests\Unit\Elevation;

use PHPUnit\Framework\TestCase;
use PhpGpxParser\Elevation\IgnElevationClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class IgnElevationClientTest extends TestCase
{
    private IgnElevationClient $client;
    private HttpClientInterface $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->client = new IgnElevationClient($this->httpClient);
    }

    public function testFetchElevationsEmptyCoords(): void
    {
        // Vérifier que l'appel avec un tableau vide renvoie un tableau vide
        $result = $this->client->fetchElevations([]);
        $this->assertEmpty($result);
    }

    public function testFetchElevationsSingleBatch(): void
    {
        // Données de test
        $coords = [
            [48.8566, 2.3522],
            [45.7640, 4.8357]
        ];

        // Préparer la réponse mock
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')
            ->willReturn(['elevations' => [42.0, 275.5]]);

        // Configurer les attentes pour le client HTTP
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://data.geopf.fr/altimetrie/1.0/calcul/alti/rest/elevation.json',
                $this->callback(function($options) {
                    $body = $options['json'];
                    return $body['lat'] === '48.8566|45.764' &&
                        $body['lon'] === '2.3522|4.8357' &&
                        $body['resource'] === 'ign_rge_alti_par_territoires';
                })
            )
            ->willReturn($response);

        // Appel de la méthode à tester
        $result = $this->client->fetchElevations($coords);

        // Vérifications
        $this->assertEquals([42.0, 275.5], $result);
    }

    public function testFetchElevationsMultipleBatches(): void
    {
        // Créer un grand nombre de coordonnées (plus que MAX_POINTS = 5000)
        $coords = [];
        for ($i = 0; $i < 6000; $i++) {
            $coords[] = [48.0 + ($i * 0.001), 2.0 + ($i * 0.001)];
        }

        // Préparer les réponses mocks pour les deux requêtes
        $response1 = $this->createMock(ResponseInterface::class);
        $response1->method('toArray')->willReturn(['elevations' => array_fill(0, 5000, 100.0)]);

        $response2 = $this->createMock(ResponseInterface::class);
        $response2->method('toArray')->willReturn(['elevations' => array_fill(0, 1000, 200.0)]);

        // Le client HTTP doit être appelé deux fois, une fois pour chaque lot
        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($response1, $response2);

        // Appel de la méthode à tester
        $result = $this->client->fetchElevations($coords);

        // Vérifications
        $this->assertCount(6000, $result);
        $this->assertEquals(array_merge(
            array_fill(0, 5000, 100.0),
            array_fill(0, 1000, 200.0)
        ), $result);
    }

    public function testFetchElevationsWithCustomResource(): void
    {
        // Données de test
        $coords = [[48.8566, 2.3522]];
        $customResource = 'custom_resource';

        // Préparer la réponse mock
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')
            ->willReturn(['elevations' => [50.0]]);

        // Vérifier que le client HTTP est appelé avec la bonne ressource
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://data.geopf.fr/altimetrie/1.0/calcul/alti/rest/elevation.json',
                $this->callback(function($options) use ($customResource) {
                    return $options['json']['resource'] === $customResource;
                })
            )
            ->willReturn($response);

        // Appel de la méthode à tester avec une ressource personnalisée
        $result = $this->client->fetchElevations($coords, $customResource);

        // Vérifications
        $this->assertEquals([50.0], $result);
    }
}
