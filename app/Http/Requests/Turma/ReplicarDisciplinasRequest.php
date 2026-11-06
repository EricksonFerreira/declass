<?php

namespace App\Http\Requests\Turma;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReplicarDisciplinasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'turma_origem_id' => [
                'required', 'integer',
                Rule::exists('turmas', 'id')->where('empresa_id', $empresaId),
            ],
            'turmas_destino' => ['required', 'array', 'min:1'],
            'turmas_destino.*' => [
                'integer', 'different:turma_origem_id',
                Rule::exists('turmas', 'id')->where('empresa_id', $empresaId),
            ],
        ];
    }
}
