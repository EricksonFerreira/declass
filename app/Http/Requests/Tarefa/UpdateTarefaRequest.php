<?php

namespace App\Http\Requests\Tarefa;

class UpdateTarefaRequest extends StoreTarefaRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // turma nao pode ser trocada apos a criacao (entregas dependem dela)
        unset($rules['turma_id'], $rules['gerar_entregas']);

        return $rules;
    }
}
