<?php

namespace App\Actions\Empresa;

use App\Models\EmpresaDocumento;
use App\Support\FiltroQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarDocumentosAction
{
    public function handle(array $filtros = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = EmpresaDocumento::query();

        FiltroQuery::aplicar($query, $filtros, ['descricao', 'numero', 'orgao_emissor', 'tipo']);

        match ($filtros['situacao']['valor'] ?? null) {
            'vigente' => $query->where('data_validade', '>', now()->addDays(30)),
            'vencendo' => $query->whereBetween('data_validade', [now(), now()->addDays(30)]),
            'vencido' => $query->where('data_validade', '<', now()),
            'sem_validade' => $query->whereNull('data_validade'),
            default => null,
        };

        return $query->orderBy('tipo')->orderByDesc('data_emissao')->paginate($perPage);
    }
}
