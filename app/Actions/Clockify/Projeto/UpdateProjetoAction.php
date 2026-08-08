<?php

namespace App\Actions\Clockify\Projeto;

use App\Models\ClockifyProjeto;

class UpdateProjetoAction
{
    public function handle(int $id, array $data): ClockifyProjeto
    {
        $projeto = ClockifyProjeto::findOrFail($id);
        // Convert formatted currency string to float if needed
        if (isset($data['valor_faturado']) && is_string($data['valor_faturado'])) {
            // Remove thousand separators and replace decimal comma with dot
            $clean = str_replace(['.', ','], ['', '.'], $data['valor_faturado']);
            $data['valor_faturado'] = (float) $clean;
        }
        $projeto->update($data);

        return $projeto;
    }
}
