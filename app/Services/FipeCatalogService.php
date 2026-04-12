<?php

namespace App\Services;

use App\Models\Marca;
use App\Models\Modelo;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FipeCatalogService
{
    public function syncCatalog(string $vehicleType = 'cars', array $brandFilters = [], bool $refreshExisting = false, bool $dryRun = false): array
    {
        $brands = collect($this->requestJson($vehicleType . '/brands'));

        if (!empty($brandFilters)) {
            $filters = array_map('trim', $brandFilters);
            $brands = $brands->filter(function (array $brand) use ($filters) {
                $code = (string) ($brand['code'] ?? $brand['codigo'] ?? '');
                $name = (string) ($brand['name'] ?? $brand['nome'] ?? '');

                return in_array($code, $filters, true) || in_array($name, $filters, true);
            })->values();
        }

        $stats = [
            'brands_api' => $brands->count(),
            'brands_created' => 0,
            'brands_updated' => 0,
            'models_api' => 0,
            'models_created' => 0,
            'models_updated' => 0,
            'models_skipped' => 0,
        ];

        foreach ($brands as $brandApi) {
            $brand = $this->syncBrand($brandApi, $refreshExisting, $dryRun, $stats);
            if (!$brand) {
                continue;
            }

            $models = collect($this->requestJson($vehicleType . '/brands/' . ($brandApi['code'] ?? $brandApi['codigo']) . '/models'));
            $stats['models_api'] += $models->count();

            foreach ($models as $modelApi) {
                $result = $this->syncModel($brand, $modelApi, $refreshExisting, $dryRun);
                $stats[$result['action']]++;
            }
        }

        return $stats;
    }

    private function syncBrand(array $brandApi, bool $refreshExisting, bool $dryRun, array &$stats): ?Marca
    {
        $codigoFipe = trim((string) ($brandApi['code'] ?? $brandApi['codigo'] ?? ''));
        $nomeFipe = trim((string) ($brandApi['name'] ?? $brandApi['nome'] ?? ''));

        if ($codigoFipe === '' || $nomeFipe === '') {
            $stats['brands_skipped'] = ($stats['brands_skipped'] ?? 0) + 1;
            return null;
        }

        $marca = Marca::query()
            ->when($codigoFipe !== '', fn ($query) => $query->where('codigo_fipe', $codigoFipe))
            ->first();

        if (!$marca) {
            $marca = Marca::query()->get()->first(function (Marca $item) use ($nomeFipe) {
                return $this->normalize((string) ($item->nome_fipe ?? $item->nome ?? '')) === $this->normalize($nomeFipe);
            });
        }

        $payload = [
            'empresa_id' => $this->currentEmpresaId(),
            'codigo_fipe' => $codigoFipe,
            'nome_fipe' => $nomeFipe,
            'ativo' => 'S',
        ];

        if (!$marca) {
            $payload['nome'] = $this->displayBrandName($nomeFipe);
            $stats['brands_created']++;

            if ($dryRun) {
                return new Marca($payload);
            }

            Marca::query()->create($payload);

            return Marca::query()->where('codigo_fipe', $codigoFipe)->first();
        }

        if ($refreshExisting || empty($marca->nome)) {
            $payload['nome'] = $this->displayBrandName($nomeFipe);
        }

        if (!$dryRun) {
            $marca->fill($payload)->save();
        }
        $stats['brands_updated']++;

        return $dryRun ? $marca : $marca->refresh();
    }

    private function syncModel(Marca $marca, array $modelApi, bool $refreshExisting, bool $dryRun): array
    {
        $codigoFipe = trim((string) ($modelApi['code'] ?? $modelApi['codigo'] ?? ''));
        $nomeFipe = trim((string) ($modelApi['name'] ?? $modelApi['nome'] ?? ''));

        if ($codigoFipe === '' || $nomeFipe === '') {
            return ['action' => 'models_skipped'];
        }

        $modelo = Modelo::query()
            ->where('marca_id', (int) $marca->id)
            ->when($codigoFipe !== '', fn ($query) => $query->where('codigo_fipe', $codigoFipe))
            ->first();

        if (!$modelo) {
            $modelo = Modelo::query()
                ->where('marca_id', (int) $marca->id)
                ->get()
                ->first(function (Modelo $item) use ($nomeFipe) {
                    $nomeLocal = (string) ($item->nome_fipe ?? $item->nome ?? '');

                    return $this->normalize($nomeLocal) === $this->normalize($nomeFipe)
                        || str_contains($this->normalize($nomeLocal), $this->normalize($nomeFipe))
                        || str_contains($this->normalize($nomeFipe), $this->normalize($nomeLocal));
                });
        }

        $payload = [
            'empresa_id' => $this->currentEmpresaId(),
            'marca_id' => (int) $marca->id,
            'codigo_fipe' => $codigoFipe,
            'nome_fipe' => $nomeFipe,
            'ativo' => 'S',
        ];

        if (!$modelo) {
            $payload['nome'] = $nomeFipe;
            if (!$dryRun) {
                Modelo::query()->create($payload);
            }

            return ['action' => 'models_created'];
        }

        if ($refreshExisting || empty($modelo->nome)) {
            $payload['nome'] = $nomeFipe;
        }

        if (!$dryRun) {
            $modelo->fill($payload)->save();
        }

        return ['action' => 'models_updated'];
    }

    private function requestJson(string $path): array
    {
        $baseUrl = rtrim((string) config('services.fipe.base_url', 'https://fipe.parallelum.com.br/api/v2'), '/');
        $token = trim((string) config('services.fipe.token', ''));
        $reference = trim((string) config('services.fipe.reference', ''));

        $client = Http::acceptJson()
            ->timeout(60)
            ->retry(2, 250);

        if ($token !== '') {
            $client = $client->withHeaders(['X-Subscription-Token' => $token]);
        }

        $query = [];
        if ($reference !== '') {
            $query['reference'] = $reference;
        }

        $response = $client->get($baseUrl . '/' . ltrim($path, '/'), $query);
        $response->throw();

        return $response->json() ?? [];
    }

    private function normalize(string $value): string
    {
        return Str::slug(mb_strtolower(trim($value)), '');
    }

    private function displayBrandName(string $name): string
    {
        $clean = trim($name);

        if (str_contains($clean, ' - ')) {
            $clean = trim((string) Str::of($clean)->after(' - '));
        }

        $clean = preg_replace('/\s+/', ' ', $clean) ?: $clean;

        return match ($clean) {
            'VW', 'Vw' => 'Volkswagen',
            'GM' => 'Chevrolet',
            'FIAT' => 'Fiat',
            'FORD' => 'Ford',
            'PEUGEOT' => 'Peugeot',
            'TOYOTA' => 'Toyota',
            'HYUNDAI' => 'Hyundai',
            'MERCEDES-BENZ', 'Mercedes Benz' => 'Mercedes-Benz',
            default => $clean,
        };
    }

    private function currentEmpresaId(): int
    {
        return (int) (app(EmpresaContext::class)->empresaId() ?? 1);
    }
}
