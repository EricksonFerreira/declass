<?php

namespace App\Http\Requests\Pagamento;

use Illuminate\Foundation\Http\FormRequest;

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
            'gateway' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'reserva_id.required' => 'O campo reserva é obrigatório',
            'reserva_id.exists' => 'A reserva informada não existe',
            'valor.required' => 'O campo valor é obrigatório',
            'valor.numeric' => 'O campo valor deve ser um número',
            'valor.min' => 'O campo valor deve ser maior que 0',
            'gateway.required' => 'O campo gateway é obrigatório',
            'gateway.string' => 'O campo gateway deve ser uma string',
        ];
    }
}
