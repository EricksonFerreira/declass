<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvariaCobrancaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dano_veiculo_ids' => 'nullable|array',
            'dano_veiculo_ids.*' => 'integer|min:1',
            'valor' => 'required|numeric|min:0.01',
            'descricao' => 'required|string|min:5|max:2000',
            'metodo' => 'nullable|string|in:calcao_capture,stripe_charge,manual',
            'payment_method_id' => 'nullable|string|max:120',
        ];
    }
}

