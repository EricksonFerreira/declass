<?php

namespace App\Services\OpenStreetMap;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OverpassLocadoraService
{
    private const ENDPOINTS = [
        'https://overpass-api.de/api/interpreter',
        'https://overpass.kumi.systems/api/interpreter',
    ];

    public function searchLocadoras(string $uf, array $cidades): array
    {
        $resultados = [];
        $encontrados = [];
        $erros = [];
        $uf = strtoupper(trim($uf));

        foreach ($cidades as $cidade) {
            try {
                foreach ($this->buscarCidade($cidade, $uf) as $elemento) {
                    $placeId = $this->buildPlaceId($elemento);

                    if ($placeId === '' || isset($encontrados[$placeId])) {
                        continue;
                    }

                    $tags = (array) data_get($elemento, 'tags', []);
                    $latitude = data_get($elemento, 'lat', data_get($elemento, 'center.lat'));
                    $longitude = data_get($elemento, 'lon', data_get($elemento, 'center.lon'));

                    $encontrados[$placeId] = true;
                    $resultados[] = [
                        'google_place_id' => $placeId,
                        'nome' => (string) ($tags['name'] ?? $tags['brand'] ?? $tags['operator'] ?? 'Locadora sem nome'),
                        'cidade' => (string) ($tags['addr:city'] ?? $tags['addr:municipality'] ?? $cidade),
                        'estado' => $uf,
                        'endereco' => $this->montarEndereco($tags),
                        'telefone' => (string) ($tags['contact:phone'] ?? $tags['phone'] ?? 'Não informado'),
                        'site' => (string) ($tags['contact:website'] ?? $tags['website'] ?? 'Não informado'),
                        'google_maps_url' => $this->montarUrlMapa($latitude, $longitude),
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'avaliacao' => null,
                        'quantidade_avaliacoes' => null,
                        'status_prospeccao' => 'Nova',
                        'consultado_google_em' => now()->toIso8601String(),
                        'origem_dados' => 'OpenStreetMap / Overpass',
                    ];
                }
            } catch (\Throwable $exception) {
                report($exception);
                $erros[] = $cidade.': '.$exception->getMessage();
            }
        }

        if ($resultados === [] && $erros !== []) {
            throw new RuntimeException('Não foi possível concluir a busca. '.implode(' | ', $erros));
        }

        return $resultados;
    }

    private function buscarCidade(string $cidade, string $uf): array
    {
        $query = $this->montarQueryOverpass($cidade, $uf);
        $ultimoErro = null;

        foreach (self::ENDPOINTS as $endpoint) {
            try {
                $response = Http::asForm()
                    ->acceptJson()
                    ->withUserAgent(config('app.name', 'DeCars').'/1.0 contato@seudominio.com.br')
                    ->connectTimeout(15)
                    ->timeout(90)
                    ->retry(2, 1500, throw: false)
                    ->post($endpoint, ['data' => $query]);

                if ($response->successful()) {
                    return (array) data_get($response->json(), 'elements', []);
                }

                $ultimoErro = 'HTTP '.$response->status();
            } catch (\Throwable $exception) {
                $ultimoErro = $exception->getMessage();
            }
        }

        throw new RuntimeException('A Overpass API não respondeu corretamente'.($ultimoErro ? ': '.$ultimoErro : '.'));
    }

    private function montarQueryOverpass(string $cidade, string $uf): string
    {
        $cidade = $this->escapeOverpass($cidade);
        $codigoEstado = 'BR-'.$this->escapeOverpass($uf);

        return <<<OVERPASS
[out:json][timeout:60];
area["ISO3166-2"="$codigoEstado"]["boundary"="administrative"]->.estado;
area(area.estado)["name"="$cidade"]["boundary"="administrative"]->.cidade;
(
  nwr["amenity"="car_rental"](area.cidade);
  nwr["office"="car_rental"](area.cidade);
  nwr["shop"="car_rental"](area.cidade);
  nwr["rental"="car"](area.cidade);
);
out center tags;
OVERPASS;
    }

    private function escapeOverpass(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], trim($value));
    }

    private function montarEndereco(array $tags): string
    {
        $logradouro = trim(implode(' ', array_filter([
            $tags['addr:street'] ?? null,
            $tags['addr:housenumber'] ?? null,
        ])));

        $endereco = implode(', ', array_filter([
            $logradouro ?: null,
            $tags['addr:suburb'] ?? $tags['addr:neighbourhood'] ?? null,
            $tags['addr:city'] ?? $tags['addr:municipality'] ?? null,
            $tags['addr:postcode'] ?? null,
        ]));

        return $endereco !== '' ? $endereco : 'Não informado';
    }

    private function montarUrlMapa(mixed $latitude, mixed $longitude): ?string
    {
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

    private function buildPlaceId(array $elemento): string
    {
        $tipo = (string) data_get($elemento, 'type', '');
        $id = (string) data_get($elemento, 'id', '');

        return $tipo !== '' && $id !== '' ? "osm:{$tipo}:{$id}" : '';
    }
}
