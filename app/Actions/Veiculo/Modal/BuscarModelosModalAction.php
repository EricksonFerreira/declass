<?php

namespace App\Actions\Veiculo\Modal;

use App\Services\ModeloService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuscarModelosModalAction
{
    public function __construct(private readonly ModeloService $modeloService) {}

    public function handle(Request $request): JsonResponse
    {
        $queryTexto = trim((string) $request->input('q', ''));
        $marcaId = (int) $request->input('marca_id', 0);
        $grupoFrotaId = (int) $request->input('grupo_frota_id', 0);

        $query = $this->modeloService->getModel()->newQuery()
            ->from('modelos')
            ->leftJoin('marcas as marcas_modelo', 'marcas_modelo.id', '=', 'modelos.marca_id')
            ->leftJoin('segmentos', 'segmentos.id', '=', 'modelos.segmento_id')
            ->select(['modelos.id','modelos.nome','modelos.codigo_fipe','modelos.nome_fipe','modelos.ano_modelo','modelos.marca_id','marcas_modelo.nome as marca_nome','segmentos.nome as segmento_nome'])
            ->where('modelos.ativo', 'S');

        if ($marcaId > 0) $query->where('modelos.marca_id', $marcaId);
        if ($grupoFrotaId > 0) {
            $query->whereHas('gruposFrota', function ($sub) use ($grupoFrotaId) {
                $sub->where('grupos_frota.id', $grupoFrotaId);
            });
        }
        if ($queryTexto !== '') {
            $query->where(function ($sub) use ($queryTexto) {
                $sub->where('modelos.nome', 'like', '%'.$queryTexto.'%')
                    ->orWhere('marcas_modelo.nome', 'like', '%'.$queryTexto.'%')
                    ->orWhere('segmentos.nome', 'like', '%'.$queryTexto.'%')
                    ->orWhere('modelos.codigo_fipe', 'like', '%'.$queryTexto.'%')
                    ->orWhere('modelos.nome_fipe', 'like', '%'.$queryTexto.'%');
            });
        }

        $items = $query->orderBy('modelos.nome')->limit(50)->get()->map(function ($modelo) {
            $partes = [];
            if (! empty($modelo->marca_nome)) $partes[] = 'Marca: '.$modelo->marca_nome;
            if (! empty($modelo->ano_modelo)) $partes[] = 'Ano modelo: '.$modelo->ano_modelo;
            if (! empty($modelo->segmento_nome)) $partes[] = 'Segmento: '.$modelo->segmento_nome;
            if (! empty($modelo->codigo_fipe)) $partes[] = 'Cód. FIPE: '.$modelo->codigo_fipe;
            return ['id' => $modelo->id, 'nome' => $modelo->nome, 'marca_id' => $modelo->marca_id, 'marca_nome' => $modelo->marca_nome, 'ano_modelo' => $modelo->ano_modelo, 'extra' => implode(' • ', $partes)];
        })->values();

        return response()->json(['items' => $items]);
    }
}
