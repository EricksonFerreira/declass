<?php

namespace App\Http\Requests\StatusVeiculo;

use Illuminate\Foundation\Http\FormRequest;

class StoreStatusVeiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'cor' => 'required|string|in:success,info,primary,warning,orange,danger,secondary,gray,grey,purple,teal',
            'ativo' => 'required|string|max:1',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            'cor.required' => 'O campo cor é obrigatório.',
            'cor.in' => 'Selecione uma cor válida para o status.',
            'ativo.required' => 'O campo ativo é obrigatório.',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere.',
        ];
    }
}
