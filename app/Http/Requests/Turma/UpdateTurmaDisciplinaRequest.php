<?php

namespace App\Http\Requests\Turma;

class UpdateTurmaDisciplinaRequest extends StoreTurmaDisciplinaRequest
{
    public function rules(): array
    {
        // Na edicao da oferta so a carga horaria e ajustavel;
        // a disciplina e identificada pela rota.
        return [
            'carga_horaria_semanal' => ['nullable', 'integer', 'min:1', 'max:40'],
        ];
    }
}
