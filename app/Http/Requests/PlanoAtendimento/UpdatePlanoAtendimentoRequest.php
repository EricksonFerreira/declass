<?php

namespace App\Http\Requests\PlanoAtendimento;

class UpdatePlanoAtendimentoRequest extends StorePlanoAtendimentoRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // aluno e escola sao imutaveis apos a criacao
        unset($rules['aluno_id'], $rules['escola_id']);

        return $rules;
    }
}
