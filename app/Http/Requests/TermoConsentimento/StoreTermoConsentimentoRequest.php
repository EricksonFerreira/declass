<?php

namespace App\Http\Requests\TermoConsentimento;

use Illuminate\Foundation\Http\FormRequest;

class StoreTermoConsentimentoRequest extends FormRequest
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
            'ordem' => 'required|integer|min:0',
            'titulo' => 'required|string|max:255',
            'texto' => 'required|string',
            'tipo' => 'required|string|in:aceito-motorista-app,aceito-reserva,checkin-motorista-app,checkout-motorista-app,checkin-reserva,checkout-reserva',
            'ativo' => 'required|string|max:1',
        ];
    }

    public function messages()
    {
        return [
            'ordem.required' => 'A ordem é obrigatória.',
            'ordem.integer' => 'A ordem deve ser um número inteiro.',
            'titulo.required' => 'O título é obrigatório.',
            'titulo.max' => 'O título deve ter no máximo 255 caracteres.',
            'texto.required' => 'O campo texto é obrigatório.',
            'texto.string' => 'O campo texto deve ser uma string.',
            'tipo.required' => 'O tipo é obrigatório.',
            'tipo.in' => 'O tipo deve ser um dos valores válidos.',
            'ativo.required' => 'O campo ativo é obrigatório',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere',
        ];
    } 
}
