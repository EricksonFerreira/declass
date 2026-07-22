<?php

namespace App\Http\Requests\OrdemServico;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdemServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'veiculo_id' => 'required|integer|exists:veiculos,id',
            'reserva_id' => 'nullable|integer|exists:reservas,id',
            'sinistro_id' => 'nullable|integer|exists:sinistros,id',
            'observacoes' => 'nullable|string|max:10000',
            'veiculo_dano_ids' => 'nullable|array',
            'veiculo_dano_ids.*' => 'integer|exists:veiculo_danos,id',
            'servicos_livres' => 'nullable|string|max:20000',
            'servicos_previstos' => 'nullable|array',
            'servicos_previstos.*' => 'required|string|max:255',
            'servicos_previstos_observacoes' => 'nullable|array',
            'servicos_previstos_observacoes.*' => 'nullable|string|max:1000',
        ];
    }
}
