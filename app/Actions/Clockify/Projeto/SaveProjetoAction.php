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

        return ClockifyProjeto::create(array_merge($data, [
            'ativo' => true,
        ]));
    }
}
