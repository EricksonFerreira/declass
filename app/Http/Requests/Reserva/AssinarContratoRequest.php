<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;

class AssinarContratoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'veiculo_id' => 'required|integer|exists:veiculos,id',
            'contrato_id' => 'required|integer|exists:contrato_checkin,id',
            'assinatura' =>'required'
        ];
    }


    public function messages()
    {
        return [
            'veiculo_id.required' => 'Você não selecionou uma veiculo.',
            'veiculo_id.integer' => 'A veiculo deve ser um número inteiro.',
            'veiculo_id.exists' => 'A veiculo não existe.',
            'contrato_id.required' => 'Você não selecionou um contrato.',
            'contrato_id.integer' => 'O contrato deve ser um número inteiro.',
            'contrato_id.exists' => 'O contrato não existe.',
            'assinatura.required' => 'A assinatura é obrigatória',
        ];
    }
}
