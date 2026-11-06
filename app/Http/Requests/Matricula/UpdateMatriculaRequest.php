<?php

namespace App\Http\Requests\Matricula;

use App\Models\Matricula;
use Illuminate\Validation\Rule;

class UpdateMatriculaRequest extends StoreMatriculaRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // Na edicao, aluno/turma sao imutaveis — so status e dados
        // complementares podem mudar.
        unset($rules['aluno_id'], $rules['turma_id']);

        $matriculaId = $this->route('matricula');
        $turmaId = Matricula::query()->whereKey($matriculaId)->value('turma_id');

        $rules['numero_chamada'] = [
            'nullable', 'integer', 'min:1', 'max:999',
            Rule::unique('matriculas', 'numero_chamada')
                ->where('turma_id', $turmaId)
                ->ignore($matriculaId, 'id'),
        ];

        return $rules;
    }
}
