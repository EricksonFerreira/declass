<?php

namespace App\Http\Requests\Calcao;

use Illuminate\Foundation\Http\FormRequest;

class AutorizarCalcaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method_id'  => 'required|string',
            'stripe_customer_id' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method_id.required' => 'O metodo de pagamento e obrigatorio para o calcao.',
        ];
    }
}
