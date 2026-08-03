<?php

namespace App\Http\Controllers\Prospeccao;

use App\Http\Controllers\Controller;
use App\Services\OpenStreetMap\OverpassLocadoraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LocadoraProspeccaoController extends Controller
{
    public function index(Request $request, OverpassLocadoraService $locadoraService)
    {
        $estado = strtoupper((string) $request->input('estado', 'PE'));
        $cidades = $this->normalizarListaCidades($request->input('cidades', []));
        $estados = $this->listaEstados();
        $estadoSelecionado = $estados[$estado] ?? null;
        $listaCidades = $cidades;
        $resultados = [];
        $erroBusca = null;

        if ($request->filled('buscar')) {
            if ($estadoSelecionado === null) {
                $erroBusca = 'Selecione um estado válido.';
            } elseif ($listaCidades === []) {
                $erroBusca = 'Selecione pelo menos uma cidade.';
            } else {
                try {
                    $resultados = $locadoraService->searchLocadoras($estado, $listaCidades);
                } catch (\Throwable $exception) {
                    report($exception);
                    $erroBusca = $exception->getMessage();
                }
            }
        }

        if ($request->expectsJson()) {
            if ($erroBusca !== null) {
                return response()->json(['message' => $erroBusca], 422);
            }

            return response()->json(['resultados' => $resultados]);
        }

        return view('prospeccao.index', compact(
            'estados',
            'estado',
            'estadoSelecionado',
            'listaCidades',
            'resultados',
            'erroBusca'
        ));
    }

    public function estados(): JsonResponse
    {
        return response()->json($this->listaEstados());
    }

    public function cidades(string $uf): JsonResponse
    {
        $uf = strtoupper(trim($uf));
        $estados = $this->listaEstados();

        if (! array_key_exists($uf, $estados)) {
            return response()->json(['message' => 'Estado inválido.'], 422);
        }

        $codigos = [
            'AC' => 12, 'AL' => 27, 'AP' => 16, 'AM' => 13, 'BA' => 29, 'CE' => 23,
            'DF' => 53, 'ES' => 32, 'GO' => 52, 'MA' => 21, 'MT' => 51, 'MS' => 50,
            'MG' => 31, 'PA' => 15, 'PB' => 25, 'PR' => 41, 'PE' => 26, 'PI' => 22,
            'RJ' => 33, 'RN' => 24, 'RS' => 43, 'RO' => 11, 'RR' => 14, 'SC' => 42,
            'SP' => 35, 'SE' => 28, 'TO' => 17,
        ];

        $cidades = Cache::remember("prospeccao.cidades.{$uf}", now()->addDay(), function () use ($codigos, $uf): array {
            try {
                $response = Http::acceptJson()
                    ->timeout(20)
                    ->get("https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$codigos[$uf]}/municipios");

                if (! $response->successful()) {
                    return [];
                }

                return collect($response->json())
                    ->map(fn ($item) => [
                        'id' => (string) data_get($item, 'id', ''),
                        'nome' => (string) data_get($item, 'nome', ''),
                    ])
                    ->filter(fn ($item) => $item['nome'] !== '')
                    ->sortBy('nome')
                    ->values()
                    ->all();
            } catch (\Throwable $exception) {
                report($exception);

                return [];
            }
        });

        return response()->json($cidades);
    }

    private function listaEstados(): array
    {
        return [
            'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
            'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo', 'GO' => 'Goiás', 'MA' => 'Maranhão',
            'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul', 'MG' => 'Minas Gerais',
            'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná', 'PE' => 'Pernambuco',
            'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima',
            'SC' => 'Santa Catarina', 'SP' => 'São Paulo', 'SE' => 'Sergipe',
            'TO' => 'Tocantins',
        ];
    }

    private function normalizarListaCidades(mixed $valor): array
    {
        $itens = is_array($valor) ? $valor : preg_split('/\r\n|\r|\n/', (string) $valor);

        return collect($itens)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
