<?php

namespace App\Services\OpenStreetMap;

use Illuminate\Support\Facades\Http;

class OverpassLocadoraService
{
    public function searchLocadoras(string $estado, array $cidades, array $termos): array
    {
        $results = [];
        $seen = [];

        foreach ($cidades as $cidade) {
            $bbox = $this->resolverBoundingBox($cidade, $estado);

            if ($bbox === null) {
                continue;
            }

            foreach ($termos as $termo) {
                $query = $this->montarQueryOverpass($bbox, $termo);
                $response = Http::timeout(60)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->post('https://overpass-api.de/api/interpreter', ['data' => $query]);

                if (! $response->successful()) {
                    continue;
                }

                foreach ((array) data_get($response->json(), 'elements', []) as $element) {
                    $placeId = $this->buildPlaceId($element);

                    if ($placeId === '' || isset($seen[$placeId])) {
                        continue;
                    }

                    $tags = (array) data_get($element, 'tags', []);
                    $lat = data_get($element, 'lat', data_get($element, 'center.lat'));
                    $lon = data_get($element, 'lon', data_get($element, 'center.lon'));

                    $seen[$placeId] = true;
                    $results[] = [
                        'google_place_id' => $placeId,
                        'nome' => (string) ($tags['name'] ?? $tags['brand'] ?? $tags['operator'] ?? 'Locadora sem nome'),
                        'cidade' => $cidade,
                        'estado' => $estado,
                        'endereco' => trim(implode(', ', array_filter([
                            $tags['addr:street'] ?? null,
                            $tags['addr:housenumber'] ?? null,
                            $tags['addr:neighbourhood'] ?? null,
                        ]))) ?: 'Não informado',
                        'telefone' => (string) ($tags['contact:phone'] ?? $tags['phone'] ?? 'Não informado'),
                        'site' => (string) ($tags['website'] ?? $tags['contact:website'] ?? 'Não informado'),
                        'google_maps_url' => 'https://www.openstreetmap.org/?mlat='.$lat.'&mlon='.$lon.'#map=18/'.($lat ?? '').'/'.($lon ?? ''),
                        'latitude' => $lat,
                        'longitude' => $lon,
                        'avaliacao' => null,
                        'quantidade_avaliacoes' => null,
                        'status_prospeccao' => 'Nova',
                        'consultado_google_em' => now()->toDateTimeString(),
                        'origem_dados' => 'OpenStreetMap / Overpass',
                    ];
                }
            }
        }

        return $results;
    }

    private function montarQueryOverpass(array $bbox, string $termo): string
    {
        [$south, $west, $north, $east] = $bbox;
        $escapedTerm = addslashes(mb_strtolower($termo));

        return <<<OVERPASS
[out:json][timeout:30];
(
  node["name"~"$escapedTerm",i]["amenity"="car_rental"]($south,$west,$north,$east);
  way["name"~"$escapedTerm",i]["amenity"="car_rental"]($south,$west,$north,$east);
  relation["name"~"$escapedTerm",i]["amenity"="car_rental"]($south,$west,$north,$east);
  node["amenity"="car_rental"]($south,$west,$north,$east);
  way["amenity"="car_rental"]($south,$west,$north,$east);
  relation["amenity"="car_rental"]($south,$west,$north,$east);
  node["office"="car_rental"]($south,$west,$north,$east);
  way["office"="car_rental"]($south,$west,$north,$east);
  relation["office"="car_rental"]($south,$west,$north,$east);
);
out center tags;
OVERPASS;
    }

    private function resolverBoundingBox(string $cidade, string $estado): ?array
    {
        $response = Http::timeout(20)->get('https://nominatim.openstreetmap.org/search', [
            'format' => 'jsonv2',
            'limit' => 1,
            'q' => $cidade.', '.$estado.', Brasil',
        ]);

        if (! $response->successful()) {
            return null;
        }

        $item = $response->json()[0] ?? null;
        if (! is_array($item) || empty($item['boundingbox'])) {
            return null;
        }

        $bbox = $item['boundingbox'];
        if (count($bbox) < 4) {
            return null;
        }

        return [
            (float) $bbox[0],
            (float) $bbox[2],
            (float) $bbox[1],
            (float) $bbox[3],
        ];
    }

    private function buildPlaceId(array $element): string
    {
        $type = (string) data_get($element, 'type', '');
        $id = (string) data_get($element, 'id', '');

        return $type !== '' && $id !== '' ? 'osm:'.$type.':'.$id : '';
    }
}
