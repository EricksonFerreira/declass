<?php

namespace App\Http\Requests\Movimentacao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovimentacaoRequest extends FormRequest
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
            'nome' => ['nullable', 'string', 'max:255'],
            'ativo' => ['required', Rule::in(['S', 'N'])],

            'veiculo_id' => ['nullable', 'integer', 'exists:veiculos,id'],
            'modelo_id' => ['nullable', 'integer', 'exists:modelos,id'],
            'tipo_movimentacao_id' => ['required', 'integer', 'exists:tipos_movimentacao,id'],
            'status_movimentacao_id' => ['required', 'integer', 'exists:status_movimentacao,id'],
            'local_origem_id' => ['nullable', 'integer', 'exists:estacoes,id'],
            'local_destino_id' => ['nullable', 'integer', 'exists:estacoes,id'],

            'ordem_servico_id' => ['nullable', 'integer', 'exists:ordens_servico,id'],
            'reserva_id' => ['nullable', 'integer', 'exists:reservas,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],

            'data_registro' => ['required', 'date'],
            'data_saida' => ['nullable', 'date'],
            'km_saida' => ['nullable', 'integer', 'min:0'],
            'data_chegada_prevista' => ['nullable', 'date'],
            'data_chegada_real' => ['nullable', 'date'],
            'km_chegada' => ['nullable', 'integer', 'min:0', 'gte:km_saida'],

            'observacoes' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'ativo.required' => 'O campo status do registro é obrigatório.',
            'ativo.in' => 'O campo status do registro deve ser Ativo ou Inativo.',
            'tipo_movimentacao_id.required' => 'O tipo de movimentação é obrigatório.',
            'status_movimentacao_id.required' => 'O status da movimentação é obrigatório.',
            'user_id.required' => 'O usuário é obrigatório.',
            'data_registro.required' => 'A data de registro é obrigatória.',
        ];
    }
}
