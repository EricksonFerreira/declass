<?php

namespace App\Http\Requests\StatusVeiculo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStatusVeiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $statusVeiculoId = $this->route('status_veiculo') ?? $this->route('id');

        return [
            'nome' => ['required', 'string', 'max:255', Rule::unique('status_veiculos', 'nome')->ignore($statusVeiculoId)],
            'cor' => 'required|string|in:success,info,primary,warning,orange,danger,secondary,gray,grey,purple,teal',
            'ativo' => 'required|string|max:1',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O campo nome deve ser um texto.',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            'nome.unique' => 'O nome informado já está em uso.',
            'cor.required' => 'O campo cor é obrigatório.',
            'cor.in' => 'Selecione uma cor válida para o status.',
            'ativo.required' => 'O campo ativo é obrigatório.',
            'ativo.string' => 'O campo ativo deve ser um texto.',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere.',
        ];
    }
}
