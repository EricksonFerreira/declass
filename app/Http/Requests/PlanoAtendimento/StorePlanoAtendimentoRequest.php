<?php

namespace App\Http\Requests\PlanoAtendimento;

use App\Models\PlanoAtendimento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanoAtendimentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'aluno_id' => [
                'required', 'integer',
                Rule::exists('alunos', 'id')->where('empresa_id', $empresaId),
            ],
            'escola_id' => [
                'required', 'integer',
                Rule::exists('escolas', 'id')->where('empresa_id', $empresaId),
            ],
            'user_id' => [
                'nullable', 'integer',
                Rule::exists('users', 'id')->where('empresa_id', $empresaId),
            ],
            'tipo' => ['required', 'string', Rule::in(array_keys(PlanoAtendimento::TIPOS))],
            'status' => ['nullable', 'string', Rule::in(array_keys(PlanoAtendimento::STATUS))],
            'data_elaboracao' => ['nullable', 'date'],
            'proxima_revisao' => ['nullable', 'date', 'after_or_equal:data_elaboracao'],
            'diagnostico' => ['nullable', 'string', 'max:4000'],
            'necessidades' => ['nullable', 'string', 'max:4000'],
            'objetivos' => ['required', 'string', 'max:4000'],
            'estrategias' => ['nullable', 'string', 'max:4000'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
