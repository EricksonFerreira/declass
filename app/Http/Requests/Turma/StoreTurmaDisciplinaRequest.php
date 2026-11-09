<?php

namespace App\Http\Requests\Turma;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTurmaDisciplinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'disciplina_id' => [
                'required', 'integer',
                Rule::exists('disciplinas', 'id')->where('empresa_id', $empresaId),
            ],
            'carga_horaria_semanal' => ['nullable', 'integer', 'min:1', 'max:40'],
            'media_aprovacao' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ];
    }
}
