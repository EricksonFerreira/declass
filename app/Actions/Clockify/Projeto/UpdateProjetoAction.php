<?php

namespace App\Actions\Clockify\Projeto;

use App\Models\ClockifyProjeto;

class UpdateProjetoAction
{
    public function handle(int $id, array $data): ClockifyProjeto
    {
        $projeto = ClockifyProjeto::findOrFail($id);

        // Parse money fields if present
        foreach (['valor_faturado', 'valor_hora'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $clean = str_replace(['.', ','], ['', '.'], preg_replace('/[^\d,.-]/', '', $data[$field]));
                $data[$field] = (float) $clean;
            }
        }

        $projeto->update($data);

        return $projeto;
    }
}
