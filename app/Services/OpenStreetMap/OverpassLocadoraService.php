<?php

namespace App\Services\OpenStreetMap;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OverpassLocadoraService
{
    private const OVERPASS_ENDPOINTS = [
        'https://overpass-api.de/api/interpreter',
        'https://overpass.kumi.systems/api/interpreter',
    ];

    public function searchLocadoras(
        string $estado,
        array $cidades,
        array $termos = []
    ): array {
        $resultados = [];
        $identificadoresEncontrados = [];
        $erros = [];

        foreach ($cidades as $cidade) {
            try {
                $bbox = $this->resolverBoundingBox($cidade, $estado);

                if ($bbox === null) {
                    $erros[] = "Não foi possível localizar os limites de {$cidade}/{$estado}.";

                    continue;
                }

                /*
                 * A busca é feita uma vez por cidade.
                 * Os termos não são necessários para encontrar os locais
                 * corretamente cadastrados como car_rental no OpenStreetMap.
                 */
                $elementos = $this->consultarOverpass($bbox);

                foreach ($elementos as $elemento) {
                    $placeId = $this->buildPlaceId($elemento);

                    if ($placeId === '' || isset($identificadoresEncontrados[$placeId])) {
                        continue;
                    }

                    $tags = (array) data_get($elemento, 'tags', []);

                    $latitude = data_get(
                        $elemento,
                        'lat',
                        data_get($elemento, 'center.lat')
                    );

                    $longitude = data_get(
                        $elemento,
                        'lon',
                        data_get($elemento, 'center.lon')
                    );

                    $identificadoresEncontrados[$placeId] = true;

                    $resultados[] = [
                        'google_place_id' => $placeId,
                        'nome' => $this->obterNome($tags),
                        'cidade' => (string) (
                            $tags['addr:city']
                            ?? $tags['addr:municipality']
                            ?? $cidade
                        ),
                        'estado' => $estado,
                        'endereco' => $this->montarEndereco($tags),
                        'telefone' => (string) (
                            $tags['contact:phone']
                            ?? $tags['phone']
                            ?? 'Não informado'
                        ),
                        'site' => (string) (
                            $tags['contact:website']
                            ?? $tags['website']
                            ?? 'Não informado'
                        ),
                        'google_maps_url' => $this->montarUrlMapa(
                            $latitude,
                            $longitude
                        ),
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'avaliacao' => null,
                        'quantidade_avaliacoes' => null,
                        'status_prospeccao' => 'Nova',
                        'consultado_google_em' => now()->toDateTimeString(),
                        'origem_dados' => 'OpenStreetMap / Overpass',
                    ];
                }
            } catch (\Throwable $exception) {
                report($exception);

                $erros[] = "{$cidade}: {$exception->getMessage()}";
            }
        }

        if ($resultados === [] && $erros !== []) {
            throw new RuntimeException(
                'A busca não pôde ser concluída. '.implode(' | ', $erros)
            );
        }

        return $resultados;
    }

    private function consultarOverpass(array $bbox): array
    {
        $query = $this->montarQueryOverpass($bbox);
        $ultimoErro = null;

        foreach (self::OVERPASS_ENDPOINTS as $endpoint) {
            try {
                /*
                 * IMPORTANTE:
                 * asForm() envia application/x-www-form-urlencoded.
                 * A Overpass espera o código da consulta no parâmetro "data".
                 */
                $response = Http::asForm()
                    ->acceptJson()
                    ->withUserAgent(config(
                        'app.openstreetmap_user_agent',
                        config('app.name').' contato@decars.com.br'
                    ))
                    ->connectTimeout(15)
                    ->timeout(90)
                    ->retry(2, 1500)
                    ->post($endpoint, [
                        'data' => $query,
                    ]);

                if ($response->successful()) {
                    return (array) data_get(
                        $response->json(),
                        'elements',
                        []
                    );
                }

                $ultimoErro = "HTTP {$response->status()} em {$endpoint}";
            } catch (RequestException|\Throwable $exception) {
                $ultimoErro = $exception->getMessage();
            }
        }

        throw new RuntimeException(
            'A Overpass API não respondeu corretamente'
            .($ultimoErro ? ": {$ultimoErro}" : '.')
        );
    }

    private function montarQueryOverpass(array $bbox): string
    {
        [$south, $west, $north, $east] = $bbox;

        return <<<OVERPASS
[out:json][timeout:60];
(
  nwr["amenity"="car_rental"]($south,$west,$north,$east);
  nwr["office"="car_rental"]($south,$west,$north,$east);
  nwr["shop"="car_rental"]($south,$west,$north,$east);
  nwr["rental"="car"]($south,$west,$north,$east);
);
out center tags;
OVERPASS;
    }

    private function resolverBoundingBox(
        string $cidade,
        string $estado
    ): ?array {
        $response = Http::acceptJson()
            ->withUserAgent(config(
                'app.openstreetmap_user_agent',
                config('app.name').' contato@example.com'
            ))
            ->connectTimeout(10)
            ->timeout(30)
            ->retry(2, 1000)
            ->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'jsonv2',
                'countrycodes' => 'br',
                'limit' => 1,
                'addressdetails' => 1,
                'q' => "{$cidade}, {$estado}, Brasil",
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                "O Nominatim retornou HTTP {$response->status()}."
            );
        }

        $item = $response->json()[0] ?? null;

        if (! is_array($item)) {
            return null;
        }

        $bbox = $item['boundingbox'] ?? null;

        /*
         * O Nominatim retorna:
         * [south, north, west, east]
         *
         * A Overpass utiliza:
         * south, west, north, east
         */
        if (! is_array($bbox) || count($bbox) < 4) {
            return null;
        }

        return [
            (float) $bbox[0],
            (float) $bbox[2],
            (float) $bbox[1],
            (float) $bbox[3],
        ];
    }

    private function obterNome(array $tags): string
    {
        return (string) (
            $tags['name']
            ?? $tags['brand']
            ?? $tags['operator']
            ?? 'Locadora sem nome'
        );
    }

    private function montarEndereco(array $tags): string
    {
        $logradouro = trim(
            implode(' ', array_filter([
                $tags['addr:street'] ?? null,
                $tags['addr:housenumber'] ?? null,
            ]))
        );

        $endereco = implode(', ', array_filter([
            $logradouro ?: null,
            $tags['addr:suburb']
                ?? $tags['addr:neighbourhood']
                ?? null,
            $tags['addr:city']
                ?? $tags['addr:municipality']
                ?? null,
            $tags['addr:postcode'] ?? null,
        ]));

        return $endereco !== '' ? $endereco : 'Não informado';
    }

    private function montarUrlMapa(
        mixed $latitude,
        mixed $longitude
    ): ?string {
        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        return sprintf(
            'https://www.openstreetmap.org/?mlat=%s&mlon=%s#map=18/%s/%s',
            $latitude,
            $longitude,
            $latitude,
            $longitude
        );
    }

    private function buildPlaceId(array $element): string
    {
        $type = (string) data_get($element, 'type', '');
        $id = (string) data_get($element, 'id', '');

        return $type !== '' && $id !== ''
            ? "osm:{$type}:{$id}"
            : '';
    }
}
