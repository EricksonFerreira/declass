<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;

class CriarContratoCheckInRequest extends FormRequest
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
        // Verifica se já existe contrato para esta reserva (modo edição)
        $reservaId = $this->route('id');
        $contratoJaExiste = \DB::table('contrato_checkin')
            ->where('reserva_id', $reservaId)
            ->exists();

        $fotoRule = $contratoJaExiste ? 'nullable|image|max:8192' : 'required|image|max:8192';

        return [
            'data_saida' => 'required|date',
            'hora_saida' => 'required|date_format:H:i',
            'marca_id' => 'nullable|integer|exists:marcas,id',
            'modelo_id' => 'nullable|integer|exists:modelos,id',
            'nivel_combustivel' => 'required|string|in:Reserva,1/8,1/4,3/8,1/2,5/8,3/4,7/8,Cheio',
            'notas_saida' => 'nullable|string|max:255',
            'observacoes_saida' => 'nullable|string|max:255',
            'quilometragem_atual' => 'required|numeric',
            'veiculo_id' => 'required|integer|exists:veiculos,id',
            'motivo_upgrade' => 'nullable|string|max:500',
            'foto_direita' => $fotoRule,
            'foto_esquerda' => $fotoRule,
            'foto_frente' => $fotoRule,
            'foto_tras' => $fotoRule,
            'foto_velocimetro' => $fotoRule,
            'foto_combustivel' => $fotoRule,
        ];
    }


    public function messages()
    {
        return [
            'data_saida.required' => 'A data de saída é obrigatória.',
            'data_saida.date' => 'A data de saída deve ser uma data válida.',
            'hora_saida.required' => 'A hora de saída é obrigatória.',
            'hora_saida.date_format' => 'A hora de saída deve ser uma hora válida.',
            'marca.required' => 'A marca é obrigatória.',
            'marca.integer' => 'A marca deve ser um número inteiro.',
            'marca.exists' => 'A marca selecionada é inválida.',
            'modelo.required' => 'O modelo é obrigatório.',
            'modelo.integer' => 'O modelo deve ser um número inteiro.',
            'modelo.exists' => 'O modelo selecionado é inválido.',
            'nivel_combustivel.required' => 'O nível de combustível é obrigatório.',
            'nivel_combustivel.string' => 'O nível de combustível deve ser uma string.',
            'nivel_combustivel.in' => 'O nível de combustível selecionado é inválido.',
            'notas_saida.string' => 'As notas de saída devem ser uma string.',
            'notas_saida.max' => 'As notas de saída não podem ter mais de 255 caracteres.',
            'observacoes_saida.string' => 'As observações de saída devem ser uma string.',
            'observacoes_saida.max' => 'As observações de saída não podem ter mais de 255 caracteres.',
            'quilometragem_atual.required' => 'A quilometragem atual é obrigatória.',
            'quilometragem_atual.numeric' => 'A quilometragem atual deve ser um número.',
            'veiculo.required' => 'Você não selecionou um veículo.',
            'veiculo.integer' => 'O veículo deve ser um número inteiro.',
            'veiculo.exists' => 'O veículo não existe.',
        ];
    }
}
