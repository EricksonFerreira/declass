<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservaRequest extends FormRequest
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
            'estacao_retirada_id' => 'required|integer|exists:estacoes,id',
            'data_inicio' => 'required|date|after_or_equal:today',
            'estacao_devolucao_id' => 'required|integer|exists:estacoes,id',
            'data_fim' => 'required|date|after:data_inicio',
            'veiculo_id' => 'nullable|integer|exists:veiculos,id',
            'modelo_id' => 'required|integer|exists:modelos,id',
            'cupom_desconto' => 'nullable|string|max:255',
            'opcionais' => 'nullable|array',
            'opcionais.*' => 'integer|exists:opcionais,id',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'estacao_retirada_id.required' => 'A estação de retirada é obrigatória.',
            'estacao_retirada_id.integer' => 'A estação de retirada deve ser um número inteiro.',
            'estacao_retirada_id.exists' => 'A estação de retirada selecionada é inválida.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.date' => 'A data de início deve ser uma data válida.',
            'data_inicio.after_or_equal' => 'A data de início deve ser hoje ou uma data futura.',
            'estacao_devolucao_id.required' => 'A estação de devolução é obrigatória.',
            'estacao_devolucao_id.integer' => 'A estação de devolução deve ser um número inteiro.',
            'estacao_devolucao_id.exists' => 'A estação de devolução selecionada é inválida.',
            'data_fim.required' => 'A data de fim é obrigatória.',
            'data_fim.date' => 'A data de fim deve ser uma data válida.',
            'data_fim.after' => 'A data de fim deve ser após a data de início.',
            'veiculo_id.required' => 'O veículo é obrigatório.',
            'veiculo_id.integer' => 'O veículo deve ser um número inteiro.',
            'veiculo_id.exists' => 'O veículo selecionado é inválido.',
            'modelo_id.required' => 'O modelo é obrigatório.',
            'modelo_id.integer' => 'O modelo deve ser um número inteiro.',
            'modelo_id.exists' => 'O modelo selecionado é inválido.',
            'cupom_desconto.string' => 'O cupom de desconto deve ser uma string.',
            'cupom_desconto.max' => 'O cupom de desconto não pode ter mais de 255 caracteres.',
            'opcionais.array' => 'Os opcionais devem ser um array.',
            'opcionais.*.integer' => 'Cada opcional deve ser um número inteiro.',
            'opcionais.*.exists' => 'O opcional selecionado é inválido.',
            'user_id.required' => 'O usuário é obrigatório.',
            'user_id.integer' => 'O usuário deve ser um número inteiro.',
            'user_id.exists' => 'O usuário selecionado é inválido.',
        ];
    }
}
