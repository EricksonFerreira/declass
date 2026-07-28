<?php

namespace App\Http\Requests\FluxoCaixa;

use Illuminate\Foundation\Http\FormRequest;

class StoreLancamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'tipo' => ['required', 'in:receita,despesa'],
            'categoria_financeira_id' => ['required', 'exists:categorias_financeiras,id'],
            'data_lancamento' => ['nullable', 'date'],
            'data_vencimento' => ['required', 'date'],
            'data_pagamento' => ['nullable', 'date'],
            'status' => ['required', 'in:pendente,pago,cancelado'],
            'metodo_pagamento' => ['nullable', 'string', 'max:50'],
            'veiculo_id' => ['nullable', 'exists:veiculos,id'],
            'reserva_id' => ['nullable', 'exists:reservas,id'],
            'observacoes' => ['nullable', 'string'],
            'recorrente' => ['nullable', 'boolean'],
            'recorrencia' => ['nullable', 'string', 'in:semanal,mensal,anual'],
            'parcelas_totais' => ['nullable', 'integer', 'min:2', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'descricao.required' => 'A descrição é obrigatória.',
            'valor.required' => 'O valor é obrigatório.',
            'valor.numeric' => 'O valor deve ser um número.',
            'valor.min' => 'O valor deve ser maior que zero.',
            'tipo.required' => 'O tipo do lançamento é obrigatório.',
            'tipo.in' => 'O tipo deve ser receita ou despesa.',
            'categoria_financeira_id.required' => 'A categoria é obrigatória.',
            'categoria_financeira_id.exists' => 'A categoria selecionada é inválida.',
            'data_lancamento.date' => 'A data do lançamento deve ser uma data válida.',
            'data_vencimento.required' => 'A data de vencimento é obrigatória.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'Status inválido.',
        ];
    }
}
