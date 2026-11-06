<?php

namespace App\Http\Requests\Avaliacao;

class UpdateAvaliacaoRequest extends StoreAvaliacaoRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // turma/disciplina imutaveis apos a criacao.
        unset($rules['turma_id'], $rules['disciplina_id']);

        return $rules;
    }
}
