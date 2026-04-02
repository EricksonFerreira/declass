<?php

namespace App\Http\Requests\Pagamento;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CobrarPagamentoRequest extends FormRequest
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
            'reserva_id' => 'required|exists:reservas,id',
            'valor' => 'required|numeric|min:0.01',
            'gateway' => ['nullable', 'string', Rule::in(['stripe', 'paypal', 'mercado_livre', 'mercadolivre'])],
        ];
    }

    public function messages(): array
    {
        return [
            'reserva_id.required' => 'O campo reserva e obrigatorio',
            'reserva_id.exists' => 'A reserva informada nao existe',
            'valor.required' => 'O campo valor e obrigatorio',
            'valor.numeric' => 'O campo valor deve ser um numero',
            'valor.min' => 'O campo valor deve ser maior que 0',
            'gateway.string' => 'O campo gateway deve ser uma string',
            'gateway.in' => 'Gateway de pagamento invalido.',
        ];
    }
}
