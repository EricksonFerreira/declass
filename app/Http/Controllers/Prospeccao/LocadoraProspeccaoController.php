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
        $estado = (string) $request->input('estado', 'PE');
        $cidades = $this->normalizarListaCidades($request->input('cidades', []));
        $termos = $this->normalizarListaTexto((string) $request->input('termos', ''));

        $estados = $this->listaEstados();
        $estadoSelecionado = $estados[$estado] ?? 'Pernambuco';
        $listaCidades = $cidades;
        $listaTermos = $termos;
        $resultados = [];
        $erroBusca = null;

        if ($request->filled('buscar')) {
            try {
                $resultados = $locadoraService->searchLocadoras($estadoSelecionado, $listaCidades, $listaTermos);
            } catch (\Throwable $e) {
                $erroBusca = $e->getMessage();
            }
        }

        return view('prospeccao.index', compact(
            'estados',
            'estado',
            'estadoSelecionado',
            'listaCidades',
            'listaTermos',
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

        if (! array_key_exists($uf, $this->listaEstados())) {
            return response()->json(['message' => 'Estado inválido.'], 422);
        }

        $cacheKey = "prospeccao.cidades.$uf";

        $cidades = Cache::remember($cacheKey, now()->addDay(), function () use ($uf): array {
            $codigos = [
                'AC' => 12, 'AL' => 27, 'AP' => 16, 'AM' => 13, 'BA' => 29, 'CE' => 23,
                'DF' => 53, 'ES' => 32, 'GO' => 52, 'MA' => 21, 'MT' => 51, 'MS' => 50,
                'MG' => 31, 'PA' => 15, 'PB' => 25, 'PR' => 41, 'PE' => 26, 'PI' => 22,
                'RJ' => 33, 'RN' => 24, 'RS' => 43, 'RO' => 11, 'RR' => 14, 'SC' => 42,
                'SP' => 35, 'SE' => 28, 'TO' => 17,
            ];

            $codigo = $codigos[$uf] ?? null;
            if ($codigo === null) {
                return [];
            }

            try {
                $response = Http::timeout(15)->get("https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$codigo}/municipios");
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
            } catch (\Throwable $e) {
                return [];
            }
        });

        return response()->json($cidades);
    }

    private function listaEstados(): array
    {
        return [
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
            'TO' => 'Tocantins',
        ];
    }

    private function normalizarListaTexto(string $texto): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $texto))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizarListaCidades(mixed $valor): array
    {
        if (is_array($valor)) {
            return collect($valor)
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->values()
                ->all();
        }

        return $this->normalizarListaTexto((string) $valor);
    }
}
