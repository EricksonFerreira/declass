<?php

namespace App\Http\Requests\Dano;

use Illuminate\Foundation\Http\FormRequest;

class DanosExistentesRequest extends FormRequest
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
            'reserva_id' => 'required|integer|exists:reservas,id',
        ];
    }

    public function messages()
    {
        return [
            'veiculo_id.required' => 'O campo veiculo_id é obrigatório',
            'veiculo_id.integer' => 'O campo veiculo_id deve ser um número inteiro',
            'veiculo_id.exists' => 'O campo veiculo_id deve existir na tabela veiculos',
            'reserva_id.required' => 'O campo reserva_id é obrigatório',
            'reserva_id.integer' => 'O campo reserva_id deve ser um número inteiro',
            'reserva_id.exists' => 'O campo reserva_id deve existir na tabela reservas',
        ];
    }
}
