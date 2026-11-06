<?php

namespace App\Http\Requests\Diario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'turma_id' => [
                'required', 'integer',
                Rule::exists('turmas', 'id')->where('empresa_id', $empresaId),
            ],
            'disciplina_id' => [
                'required', 'integer',
                Rule::exists('disciplinas', 'id')->where('empresa_id', $empresaId),
            ],
            'data' => ['required', 'date'],
            'conteudo' => ['required', 'string', 'max:10000'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
