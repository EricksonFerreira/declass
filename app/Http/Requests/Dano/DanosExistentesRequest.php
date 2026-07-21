<?php

namespace App\Http\Requests\Dano;

use Illuminate\Foundation\Http\FormRequest;

class DanosExistentesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'veiculo_id' => 'required|integer|exists:veiculos,id',
            'codigo'     => 'nullable|string',
            'reserva_id' => 'nullable',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->filled('codigo') && !$this->filled('reserva_id')) {
                $validator->errors()->add('codigo', 'Informe o código da reserva.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'veiculo_id.required' => 'O campo veiculo_id é obrigatório',
            'veiculo_id.integer'  => 'O campo veiculo_id deve ser um número inteiro',
            'veiculo_id.exists'   => 'O campo veiculo_id deve existir na tabela veiculos',
        ];
    }

    public function getCodigo(): string
    {
        return (string) ($this->input('codigo') ?? $this->input('reserva_id'));
    }
}
