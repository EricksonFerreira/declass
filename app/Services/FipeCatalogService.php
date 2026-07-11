<?php

namespace App\Services;

use App\Models\Marca;
use App\Models\Modelo;
use App\Support\EmpresaContext;
use App\Support\SchemaCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FipeCatalogService
{
    /**
     * @param string|array<int, string> $vehicleTypes
     */
    public function syncCatalog(string|array $vehicleTypes = 'cars', array $brandFilters = [], bool $refreshExisting = false, bool $dryRun = false): array
    {
        $stats = [
            'brands_api' => 0,
            'brands_created' => 0,
            'brands_updated' => 0,
            'models_api' => 0,
            'models_created' => 0,
            'models_updated' => 0,
            'models_skipped' => 0,
            'brands_skipped' => 0,
        ];

        foreach ($this->normalizeVehicleTypes($vehicleTypes) as $vehicleType) {
            $brands = collect($this->fetchJsonForVehicleType($vehicleType, 'brands'));

            if (!empty($brandFilters)) {
                $filters = array_map('trim', $brandFilters);
                $brands = $brands->filter(function (array $brand) use ($filters) {
                    $code = (string) ($brand['code'] ?? $brand['codigo'] ?? '');
                    $name = (string) ($brand['name'] ?? $brand['nome'] ?? '');

                    return in_array($code, $filters, true) || in_array($name, $filters, true);
                })->values();
            }

            $stats['brands_api'] += $brands->count();

            foreach ($brands as $brandApi) {
                $brand = $this->syncBrand($vehicleType, $brandApi, $refreshExisting, $dryRun, $stats);
                if (!$brand) {
                    continue;
                }

                $models = collect($this->fetchJsonForVehicleType(
                    $vehicleType,
                    'brands/' . ($brandApi['code'] ?? $brandApi['codigo']) . '/models'
                ));
                $stats['models_api'] += $models->count();

                foreach ($models as $modelApi) {
                    $result = $this->syncModel($vehicleType, $brand, $modelApi, $refreshExisting, $dryRun);
                    $stats[$result['action']]++;
                }
            }
        }

        return $stats;
    }

    private function syncBrand(string $vehicleType, array $brandApi, bool $refreshExisting, bool $dryRun, array &$stats): ?Marca
    {
        $codigoFipe = trim((string) ($brandApi['code'] ?? $brandApi['codigo'] ?? ''));
        $nomeFipe = trim((string) ($brandApi['name'] ?? $brandApi['nome'] ?? ''));

        if ($codigoFipe === '' || $nomeFipe === '') {
            $stats['brands_skipped'] = ($stats['brands_skipped'] ?? 0) + 1;
            return null;
        }

        $marca = Marca::query()
            ->when($this->supportsVehicleType('marcas'), fn ($query) => $query->where('vehicle_type', $vehicleType))
            ->when($codigoFipe !== '', fn ($query) => $query->where('codigo_fipe', $codigoFipe))
            ->first();

        if (!$marca) {
            $marca = Marca::query()
                ->when($this->supportsVehicleType('marcas'), fn ($query) => $query->where('vehicle_type', $vehicleType))
                ->get()
                ->first(function (Marca $item) use ($nomeFipe) {
                return $this->normalize((string) ($item->nome_fipe ?? $item->nome ?? '')) === $this->normalize($nomeFipe);
                });
        }

        $payload = [
            'empresa_id' => $this->currentEmpresaId(),
            'codigo_fipe' => $codigoFipe,
            'nome_fipe' => $nomeFipe,
            'ativo' => 'S',
        ];
        if ($this->supportsVehicleType('marcas')) {
            $payload['vehicle_type'] = $vehicleType;
        }

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

    private function syncModel(string $vehicleType, Marca $marca, array $modelApi, bool $refreshExisting, bool $dryRun): array
    {
        $codigoFipe = trim((string) ($modelApi['code'] ?? $modelApi['codigo'] ?? ''));
        $nomeFipe = trim((string) ($modelApi['name'] ?? $modelApi['nome'] ?? ''));

        if ($codigoFipe === '' || $nomeFipe === '') {
            return ['action' => 'models_skipped'];
        }

        $modelo = Modelo::query()
            ->where('marca_id', (int) $marca->id)
            ->when($this->supportsVehicleType('modelos'), fn ($query) => $query->where('vehicle_type', $vehicleType))
            ->when($codigoFipe !== '', fn ($query) => $query->where('codigo_fipe', $codigoFipe))
            ->first();

        if (!$modelo) {
            $modelo = Modelo::query()
                ->where('marca_id', (int) $marca->id)
                ->when($this->supportsVehicleType('modelos'), fn ($query) => $query->where('vehicle_type', $vehicleType))
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
        if ($this->supportsVehicleType('modelos')) {
            $payload['vehicle_type'] = $vehicleType;
        }

        $brandCode = (string) ($marca->codigo_fipe ?? '');
        $anoModelo = $this->resolveModelYear(
            $vehicleType,
            $brandCode,
            $codigoFipe
        );
        if ($anoModelo !== null) {
            $payload['ano_modelo'] = $anoModelo;
        }

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

    private function resolveModelYear(string $vehicleType, string $brandCode, string $modelCode): ?int
    {
        if ($brandCode === '' || $modelCode === '') {
            return null;
        }

        try {
            $years = collect($this->fetchJsonForVehicleType($vehicleType, 'brands/' . $brandCode . '/models/' . $modelCode . '/years'));
        } catch (\Throwable) {
            return null;
        }

        $yearValues = $years
            ->map(function (array $item) {
                $raw = (string) ($item['name'] ?? $item['nome'] ?? $item['code'] ?? $item['codigo'] ?? '');
                if ($raw === '') {
                    return null;
                }

                if (preg_match('/(\d{4})/', $raw, $matches)) {
                    return (int) $matches[1];
                }

                return null;
            })
            ->filter()
            ->values();

        return $yearValues->isEmpty() ? null : (int) $yearValues->max();
    }

    private function requestJson(string $path, ?string $baseUrl = null): array
    {
        $baseUrl = rtrim((string) ($baseUrl ?? config('services.fipe.base_url', 'https://fipe.parallelum.com.br/api/v2')), '/');
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

    /**
     * @return array<int, string>
     */
    private function normalizeVehicleTypes(string|array $vehicleTypes): array
    {
        $items = is_array($vehicleTypes) ? $vehicleTypes : explode(',', $vehicleTypes);
        $items = array_values(array_filter(array_map(
            fn ($item) => $this->normalizeVehicleType((string) $item),
            $items
        )));

        if (empty($items)) {
            $fallback = config('services.fipe.vehicle_types', ['cars']);
            $items = array_values(array_filter(array_map(
                fn ($item) => $this->normalizeVehicleType((string) $item),
                is_array($fallback) ? $fallback : [$fallback]
            )));
        }

        return array_values(array_unique($items));
    }

    private function normalizeVehicleType(string $vehicleType): string
    {
        $vehicleType = trim(mb_strtolower($vehicleType));

        return match ($vehicleType) {
            'car', 'carro', 'carros', 'cars' => 'cars',
            'moto', 'motos', 'motorcycle', 'motorcycles' => 'motorcycles',
            'truck', 'trucks', 'truk', 'truks', 'caminhao', 'caminhoes', 'caminhão', 'caminhões' => 'trucks',
            default => $vehicleType,
        };
    }

    /**
     * @return array<int, array{base_url: string, path: string}>
     */
    private function vehicleTypeEndpoints(string $vehicleType): array
    {
        $vehicleType = $this->normalizeVehicleType($vehicleType);
        $modernBase = rtrim((string) config('services.fipe.base_url', 'https://fipe.parallelum.com.br/api/v2'), '/');
        $legacyBase = rtrim((string) config('services.fipe.legacy_base_url', 'https://parallelum.com.br/fipe/api/v1'), '/');

        return match ($vehicleType) {
            'motorcycles' => [
                ['base_url' => $modernBase, 'path' => 'motorcycles'],
                ['base_url' => $modernBase, 'path' => 'motos'],
                ['base_url' => $legacyBase, 'path' => 'motos'],
            ],
            'trucks' => [
                ['base_url' => $modernBase, 'path' => 'trucks'],
                ['base_url' => $modernBase, 'path' => 'caminhoes'],
                ['base_url' => $legacyBase, 'path' => 'caminhoes'],
            ],
            default => [
                ['base_url' => $modernBase, 'path' => 'cars'],
                ['base_url' => $modernBase, 'path' => 'carros'],
                ['base_url' => $legacyBase, 'path' => 'carros'],
            ],
        };
    }

    public function fetchJsonForVehicleType(string $vehicleType, string $path): array
    {
        $lastException = null;

        foreach ($this->vehicleTypeEndpoints($vehicleType) as $endpoint) {
            try {
                return $this->requestJson(
                    $endpoint['path'] . '/' . ltrim($path, '/'),
                    $endpoint['base_url']
                );
            } catch (\Throwable $exception) {
                $lastException = $exception;
            }
        }

        if ($lastException) {
            throw $lastException;
        }

        return [];
    }

    private function supportsVehicleType(string $table): bool
    {
        return SchemaCache::hasColumn($table, 'vehicle_type');
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
