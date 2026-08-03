<?php

namespace App\Services\Google;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class GooglePlacesService
{
    public function searchLocadoras(string $estado, array $cidades, array $termos): array
    {
        $apiKey = (string) config('services.google_places.api_key', '');

        if ($apiKey === '') {
            throw new \RuntimeException('Chave da Google Places API nao configurada.');
        }

        $results = [];
        $seen = [];

        foreach ($cidades as $cidade) {
            foreach ($termos as $termo) {
                $query = trim(sprintf('%s em %s, %s', $termo, $cidade, $estado));

                $response = Http::withHeaders([
                    'X-Goog-Api-Key' => $apiKey,
                    'X-Goog-FieldMask' => 'places.id,places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.websiteUri,places.rating,places.userRatingCount,places.googleMapsUri,places.location',
                ])->post('https://places.googleapis.com/v1/places:searchText', [
                    'textQuery' => $query,
                    'languageCode' => 'pt-BR',
                    'regionCode' => 'BR',
                    'maxResultCount' => 20,
                ]);

                if (! $response->successful()) {
                    continue;
                }

                foreach ((array) $response->json('places', []) as $place) {
                    $placeId = (string) data_get($place, 'id', '');

                    if ($placeId === '' || isset($seen[$placeId])) {
                        continue;
                    }

                    $seen[$placeId] = true;

                    $results[] = [
                        'google_place_id' => $placeId,
                        'nome' => (string) data_get($place, 'displayName.text', ''),
                        'cidade' => $cidade,
                        'estado' => $estado,
                        'endereco' => (string) data_get($place, 'formattedAddress', ''),
                        'telefone' => (string) data_get($place, 'nationalPhoneNumber', 'Não informado'),
                        'site' => (string) data_get($place, 'websiteUri', 'Não informado'),
                        'google_maps_url' => (string) data_get($place, 'googleMapsUri', ''),
                        'latitude' => data_get($place, 'location.latitude'),
                        'longitude' => data_get($place, 'location.longitude'),
                        'avaliacao' => data_get($place, 'rating'),
                        'quantidade_avaliacoes' => data_get($place, 'userRatingCount'),
                        'status_prospeccao' => 'Nova',
                        'consultado_google_em' => now()->toDateTimeString(),
                    ];
                }
            }
        }

        return $results;
    }
}
