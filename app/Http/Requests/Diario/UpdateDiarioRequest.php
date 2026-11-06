<?php

namespace App\Http\Requests\Diario;

use Illuminate\Validation\Rule;

class UpdateDiarioRequest extends StoreDiarioRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // turma/disciplina sao imutaveis apos a criacao da aula.
        unset($rules['turma_id'], $rules['disciplina_id']);

        // unicidade (turma, disciplina, data) ignorando o proprio registro
        $diario = \App\Models\Diario::query()->find($this->route('diario'));
        if ($diario) {
            $rules['data'] = [
                'required', 'date',
                Rule::unique('diarios', 'data')
                    ->where('turma_id', $diario->turma_id)
                    ->where('disciplina_id', $diario->disciplina_id)
                    ->ignore($diario->id, 'id'),
            ];
        }

        return $rules;
    }
}
