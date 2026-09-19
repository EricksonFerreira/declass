<?php

namespace App\Actions\Licitacao;

use App\Models\Licitacao;
use App\Support\EmpresaContext;

class SaveLicitacaoAction
{
    public function handle(array $data): Licitacao
    {
        return Licitacao::create(array_merge($data, [
            // Explícito: garante o tenant mesmo se o hook creating não
            // registrar (SchemaCache velho) ou em contexto console/queue.
            'empresa_id' => app(EmpresaContext::class)->empresaId() ?? auth()->user()?->empresa_id,
            'status' => 'cadastrada',
            'usuinc' => auth()->id(),
            'datainc' => now(),
        ]));
    }
}
