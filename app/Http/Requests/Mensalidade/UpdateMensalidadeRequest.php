<?php

namespace App\Http\Requests\Mensalidade;

use App\Models\Mensalidade;
use Illuminate\Validation\Rule;

class UpdateMensalidadeRequest extends StoreMensalidadeRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $id = $this->route('mensalidade') ?? $this->route('id');

        // matricula_id e competencia ficam imutaveis apos a criacao;
        // unicidade da competencia passa a ignorar o proprio registro.
        unset($rules['matricula_id'], $rules['competencia']);

        $rules['status'] = ['nullable', Rule::in(array_keys(Mensalidade::STATUS))];
        $rules['remover_boleto'] = ['nullable', 'boolean'];

        return $rules;
    }
}
