<?php

namespace App\Http\Requests\TransporteAluno;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransporteAlunoRequest extends FormRequest
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
            'rota_id' => [
                'required', 'integer',
                Rule::exists('rotas_transporte', 'id')->where('empresa_id', $empresaId),
            ],
            'parada_id' => [
                'nullable', 'integer',
                Rule::exists('paradas_transporte', 'id')->where('empresa_id', $empresaId),
            ],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
