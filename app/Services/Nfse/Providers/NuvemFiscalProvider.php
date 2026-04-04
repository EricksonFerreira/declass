<?php

namespace App\Services\Nfse\Providers;

use App\Models\Empresa;
use App\Models\Nfse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NuvemFiscalProvider implements NfseProviderInterface
{
    public function emitir(Empresa $empresa, array $payload): array
    {
        $token = $this->getToken($empresa);
        if (!$token) {
            return [
                'status' => 'error',
                'response' => ['message' => 'Nao foi possivel autenticar na Nuvem Fiscal.'],
            ];
        }

        $baseUrl = $this->baseUrl($empresa);
        $response = Http::withToken($token)->post($baseUrl . '/nfse/dps', $payload);
        $data = $response->json();

        if (!$response->successful()) {
            return [
                'status' => 'error',
                'response' => $data ?: ['message' => 'Falha ao emitir NFS-e.'],
            ];
        }

        return [
            'status' => 'success',
            'response' => $data,
        ];
    }

    public function cancelar(Empresa $empresa, Nfse $nfse, string $motivo): array
    {
        $token = $this->getToken($empresa);
        if (!$token) {
            return [
                'status' => 'error',
                'response' => ['message' => 'Nao foi possivel autenticar na Nuvem Fiscal para cancelar.'],
            ];
        }

        $baseUrl = $this->baseUrl($empresa);
        $payload = [
            'codigo' => '9',
            'motivo' => $motivo,
        ];
        $response = Http::withToken($token)->post($baseUrl . '/nfse/' . $nfse->nfse_id . '/cancelamento', $payload);
        $data = $response->json();

        if (!$response->successful()) {
            return [
                'status' => 'error',
                'response' => $data ?: ['message' => 'Falha ao cancelar NFS-e.'],
            ];
        }

        return [
            'status' => 'success',
            'response' => $data,
        ];
    }

    public function consultar(Empresa $empresa, string $nfseId): array
    {
        $token = $this->getToken($empresa);
        if (!$token) {
            return [
                'status' => 'error',
                'response' => ['message' => 'Nao foi possivel autenticar na Nuvem Fiscal para consultar.'],
            ];
        }

        $baseUrl = $this->baseUrl($empresa);
        $response = Http::withToken($token)->get($baseUrl . '/nfse/' . $nfseId);
        $data = $response->json();

        if (!$response->successful()) {
            return [
                'status' => 'error',
                'response' => $data ?: ['message' => 'Falha ao consultar NFS-e.'],
            ];
        }

        return [
            'status' => 'success',
            'response' => $data,
        ];
    }

    public function baixarXml(Empresa $empresa, string $nfseId, string $tipo = 'xml'): array
    {
        $token = $this->getToken($empresa);
        if (!$token) {
            return [
                'status' => 'error',
                'message' => 'Nao foi possivel autenticar na Nuvem Fiscal.',
            ];
        }

        $endpoint = $tipo === 'dps'
            ? '/nfse/' . $nfseId . '/xml/dps'
            : '/nfse/' . $nfseId . '/xml';

        $baseUrl = $this->baseUrl($empresa);
        $response = Http::withToken($token)->get($baseUrl . $endpoint);
        if (!$response->successful()) {
            return [
                'status' => 'error',
                'message' => 'Falha ao baixar XML da NFS-e.',
                'response' => $response->json(),
            ];
        }

        $contentType = (string) $response->header('Content-Type');
        $body = $response->body();

        if (str_contains($contentType, 'application/json')) {
            $json = $response->json();
            if (!empty($json['url'])) {
                $fileResponse = Http::get($json['url']);
                if ($fileResponse->successful()) {
                    $contentType = (string) $fileResponse->header('Content-Type');
                    $body = $fileResponse->body();
                }
            }
        }

        return [
            'status' => 'success',
            'content' => $body,
            'content_type' => $contentType ?: 'application/xml',
        ];
    }

    private function getToken(Empresa $empresa): ?string
    {
        $clientId = trim((string) ($empresa->nfse_client_id ?? ''));
        $clientSecret = trim((string) ($empresa->nfse_client_secret ?? ''));
        if ($clientId === '' || $clientSecret === '') {
            return null;
        }

        $cacheKey = 'nfse_token_' . $empresa->id;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $authUrl = (string) config('services.nuvemfiscal.auth_url');
        $response = Http::asForm()->post($authUrl, [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();
        $token = $data['access_token'] ?? null;
        $expiresIn = (int) ($data['expires_in'] ?? 0);
        if (!$token) {
            return null;
        }

        if ($expiresIn > 60) {
            Cache::put($cacheKey, $token, now()->addSeconds($expiresIn - 60));
        } else {
            Cache::put($cacheKey, $token, now()->addMinutes(30));
        }

        return $token;
    }

    private function baseUrl(Empresa $empresa): string
    {
        $ambiente = strtolower(trim((string) ($empresa->nfse_ambiente ?? 'homologacao')));
        if ($ambiente === 'homologacao') {
            return (string) config('services.nuvemfiscal.sandbox_url');
        }

        return (string) config('services.nuvemfiscal.base_url');
    }
}
