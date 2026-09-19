<?php

namespace App\Actions\Clockify\Projeto;

use App\Models\ClockifyProjeto;

class SaveProjetoAction
{
    public function handle(array $data): ClockifyProjeto
    {
        // Parse money fields if present
        foreach (['valor_faturado', 'valor_hora'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $clean = str_replace(['.', ','], ['', '.'], preg_replace('/[^\d,.-]/', '', $data[$field]));
                $data[$field] = (float) $clean;
            }
        }

        // Numero sequencial por empresa+ano: preenche sozinho quando o
        // usuario informa o ano e deixa o numero em branco.
        if (! empty($data['ano']) && empty($data['numero'])) {
            $data['numero'] = (int) ClockifyProjeto::withoutGlobalScope('empresa')
                ->where('empresa_id', app(\App\Support\EmpresaContext::class)->empresaId() ?? auth()->user()?->empresa_id)
                ->where('ano', (int) $data['ano'])
                ->max('numero') + 1;
        }

        return ClockifyProjeto::create(array_merge($data, [
            'ativo' => true,
        ]));
    }
}
