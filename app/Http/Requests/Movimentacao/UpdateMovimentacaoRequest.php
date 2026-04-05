<?php

namespace App\Http\Requests\Movimentacao;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMovimentacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $movimentacaoId = $this->route('movimentacao');

        return [
            'nome' => 'required|string|max:255|unique:movimentacoes,nome,' . $movimentacaoId,
            'ativo' => 'required|string|max:1',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome e obrigatorio.',
            'nome.string' => 'O campo nome deve ser uma string.',
            'nome.max' => 'O campo nome deve ter no maximo 255 caracteres.',
            'nome.unique' => 'O nome informado ja esta em uso.',
            'ativo.required' => 'O campo ativo e obrigatorio.',
            'ativo.string' => 'O campo ativo deve ser uma string.',
            'ativo.max' => 'O campo ativo deve ter no maximo 1 caractere.',
        ];
    }
}
