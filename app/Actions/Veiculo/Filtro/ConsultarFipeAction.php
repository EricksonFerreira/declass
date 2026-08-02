<?php

namespace App\Actions\Veiculo\Filtro;

use App\Support\EmpresaContext;
use App\Services\FipeCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Marca;
use App\Models\Modelo;
use Illuminate\Support\Facades\Auth;

class ConsultarFipeAction
{
    public function __construct(private readonly FipeCatalogService $fipeCatalogService) {}

    public function handle(Request $request): JsonResponse
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? Auth::user()?->empresa_id;

        $marcaExists = Rule::exists('marcas', 'id');
        $modeloExists = Rule::exists('modelos', 'id');

        if ($empresaId !== null) {
            $marcaExists = $marcaExists->where(fn ($query) => $query->where('empresa_id', (int) $empresaId));
            $modeloExists = $modeloExists->where(fn ($query) => $query->where('empresa_id', (int) $empresaId));
        }

        $dados = $request->validate([
            'marca_id' => ['required', 'integer', $marcaExists],
            'modelo_id' => ['required', 'integer', $modeloExists],
            'ano_modelo' => ['nullable', 'integer', 'min:1900', 'max:' . (now()->year + 2)],
            'ano_fabricacao' => ['nullable', 'integer', 'min:1900', 'max:' . (now()->year + 2)],
        ]);

        $marca = Marca::query()->find($dados['marca_id']);
        $modelo = Modelo::query()->find($dados['modelo_id']);

        if (! $marca || ! $modelo) {
            return response()->json(['message' => 'Marca ou modelo não encontrado no cadastro local.'], 422);
        }

        $anoModelo = (int) ($dados['ano_modelo'] ?? $dados['ano_fabricacao'] ?? 0);
        if ($anoModelo <= 0) {
            $anoModelo = (int) ($modelo->ano_modelo ?? 0);
        }
        if ($anoModelo <= 0) {
            return response()->json(['message' => 'Informe o ano do veículo para consultar a FIPE.'], 422);
        }

        $vehicleType = (string) ($modelo->vehicle_type ?? $marca->vehicle_type ?? config('services.fipe.vehicle_type', 'cars'));

        try {
            $marcas = $this->fipeCatalogService->fetchJsonForVehicleType($vehicleType, 'brands');
            $marcaApi = collect($marcas)->first(function (array $item) use ($marca) {
                $nomeApi = Str::slug((string) ($item['name'] ?? $item['nome'] ?? ''), '');
                $nomeSistema = Str::slug((string) ($marca->nome_fipe ?? $marca->nome ?? ''), '');
                return $nomeApi === $nomeSistema || str_contains($nomeApi, $nomeSistema) || str_contains($nomeSistema, $nomeApi);
            });

            if (! $marcaApi) {
                return response()->json(['message' => 'Nao foi possivel localizar a marca na FIPE.'], 422);
            }

            $codigoMarcaApi = (string) ($marcaApi['code'] ?? $marcaApi['codigo'] ?? '');
            $modelosResponse = $this->fipeCatalogService->fetchJsonForVehicleType($vehicleType, 'brands/'.$codigoMarcaApi.'/models');
            $modeloApi = collect($modelosResponse)->first(function (array $item) use ($modelo) {
                $nomeApi = Str::slug((string) ($item['name'] ?? $item['nome'] ?? ''), '');
                $nomeSistema = Str::slug((string) ($modelo->nome_fipe ?? $modelo->nome ?? ''), '');
                return $nomeApi === $nomeSistema || str_contains($nomeApi, $nomeSistema) || str_contains($nomeSistema, $nomeApi);
            });

            if (! $modeloApi) {
                return response()->json(['message' => 'Nao foi possivel localizar o modelo na FIPE.'], 422);
            }

            $codigoModeloApi = (string) ($modeloApi['code'] ?? $modeloApi['codigo'] ?? '');
            $anosResponse = $this->fipeCatalogService->fetchJsonForVehicleType($vehicleType, 'brands/'.$codigoMarcaApi.'/models/'.$codigoModeloApi.'/years');
            $anoApi = collect($anosResponse)->first(fn (array $item) => Str::contains((string) ($item['name'] ?? ''), (string) $anoModelo));
            if (! $anoApi) {
                return response()->json(['message' => 'Nao foi possivel localizar o ano/modelo na FIPE.'], 422);
            }

            $detalhe = $this->fipeCatalogService->fetchJsonForVehicleType($vehicleType, 'brands/'.$codigoMarcaApi.'/models/'.$codigoModeloApi.'/years/'.$anoApi['code']);
            return response()->json([
                'marca' => $marcaApi['name'] ?? $marca->nome,
                'modelo' => $modeloApi['name'] ?? $modelo->nome,
                'ano_modelo' => $anoModelo,
                'codigo_fipe' => $detalhe['codeFipe'] ?? $detalhe['code'] ?? null,
                'referencia_fipe' => $detalhe['referenceMonth'] ?? $detalhe['reference'] ?? null,
                'valor_fipe' => null,
                'valor_fipe_formatado' => $detalhe['price'] ?? $detalhe['Valor'] ?? null,
                'combustivel' => $detalhe['fuel'] ?? null,
                'ano_fipe' => $detalhe['modelYear'] ?? null,
                'data_consulta_fipe' => now()->format('Y-m-d'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Falha ao consultar a FIPE. Tente novamente.'], 500);
        }
    }
}
