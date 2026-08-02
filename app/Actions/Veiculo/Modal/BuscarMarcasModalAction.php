<?php

namespace App\Actions\Veiculo\Modal;

use App\Services\MarcaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuscarMarcasModalAction
{
    public function __construct(private readonly MarcaService $marcaService) {}

    public function handle(Request $request): JsonResponse
    {
        $queryTexto = trim((string) $request->input('q', ''));
        $grupoFrotaId = (int) $request->input('grupo_frota_id', 0);

        $query = $this->marcaService->getModel()->newQuery()
            ->from('marcas')
            ->select(['id', 'nome', 'nome_fipe', 'codigo_fipe'])
            ->where('ativo', 'S');

        if ($grupoFrotaId > 0) {
            $query->whereHas('modelos', function ($sub) use ($grupoFrotaId) {
                $sub->where('modelos.ativo', 'S')
                    ->whereHas('gruposFrota', function ($grupos) use ($grupoFrotaId) {
                        $grupos->where('grupos_frota.id', $grupoFrotaId);
                    });
            });
        }

        if ($queryTexto !== '') {
            $query->where(function ($sub) use ($queryTexto) {
                $sub->where('marcas.nome', 'like', '%'.$queryTexto.'%')
                    ->orWhere('marcas.nome_fipe', 'like', '%'.$queryTexto.'%')
                    ->orWhere('marcas.codigo_fipe', 'like', '%'.$queryTexto.'%');
            });
        }

        $items = $query->orderBy('marcas.nome')->limit(50)->get()->map(function ($marca) {
            $partes = [];
            if (! empty($marca->codigo_fipe)) $partes[] = 'Cód. FIPE: '.$marca->codigo_fipe;
            if (! empty($marca->nome_fipe) && $marca->nome_fipe !== $marca->nome) $partes[] = 'FIPE: '.$marca->nome_fipe;
            return ['id' => $marca->id, 'nome' => $marca->nome, 'extra' => implode(' • ', $partes)];
        })->values();

        return response()->json(['items' => $items]);
    }
}
