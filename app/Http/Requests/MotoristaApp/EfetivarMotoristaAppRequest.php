<?php

namespace App\Http\Requests\MotoristaApp;

use Illuminate\Foundation\Http\FormRequest;

class EfetivarMotoristaAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
            'quilometragem_inicial' => ['required', 'integer', 'min:0'],
            'nivel_combustivel_inicial' => ['required', 'string', 'in:Reserva,1/8,1/4,3/8,1/2,5/8,3/4,7/8,Cheio'],
            'data_retirada_real' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'veiculo_id.required' => 'Selecione um veículo.',
            'quilometragem_inicial.required' => 'Informe a quilometragem inicial.',
            'quilometragem_inicial.min' => 'Quilometragem não pode ser negativa.',
            'nivel_combustivel_inicial.required' => 'Informe o nível de combustível.',
            'nivel_combustivel_inicial.in' => 'Nível de combustível inválido.',
        ];
    }
}
