<?php

namespace App\Http\Requests\Estacao;

use Illuminate\Foundation\Http\FormRequest;

class StoreEstacaoRequest extends FormRequest
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
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefone' => 'required|string|max:255',
            'celular' => [
                'required',
                'string',
                'max:20',
                'regex:/^\(\d{2}\) \d{5}-\d{4}$/'
            ],
            'cidade' => 'required|string|max:255',
            'numero' => 'required|string',
            'zip_code' => 'required|string|max:255',
            'latitude' => 'required|string|max:255',
            'longitude' => 'required|string|max:255',
            'ativo' => 'required|string|max:1',
            'checkin' => 'required|string|max:1',
            'checkout' => 'required|string|max:1',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O campo nome deve ser uma string.',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'O campo email deve ser um email válido.',
            'email.max' => 'O campo email deve ter no máximo 255 caracteres.',
            'telefone.required' => 'O campo telefone é obrigatório.',
            'telefone.string' => 'O campo telefone deve ser uma string.',
            'telefone.max' => 'O campo telefone deve ter no máximo 255 caracteres.',
            'celular.required' => 'O campo celular é obrigatório.',
            'celular.string' => 'O campo celular deve ser uma string.',
            'celular.max' => 'O campo celular deve ter no máximo 20 caracteres.',
            'celular.regex' => 'O campo celular deve ser um número de telefone válido.',
            'cidade.required' => 'O campo cidade é obrigatório.',
            'cidade.string' => 'O campo cidade deve ser uma string.',
            'cidade.max' => 'O campo cidade deve ter no máximo 255 caracteres.',
            'numero.required' => 'O campo número é obrigatório.',
            'numero.string' => 'O campo número deve ser uma string.',
            'zip_code.required' => 'O campo zipcode é obrigatório.',
            'zip_code.string' => 'O campo zipcode deve ser uma string.',
            'zip_code.max' => 'O campo zipcode deve ter no máximo 255 caracteres.',
            'latitude.required' => 'O campo latitude é obrigatório.',
            'latitude.string' => 'O campo latitude deve ser uma string.',
            'latitude.max' => 'O campo latitude deve ter no máximo 255 caracteres.',
            'longitude.required' => 'O campo longitude é obrigatório.',
            'longitude.string' => 'O campo longitude deve ser uma string.',
            'longitude.max' => 'O campo longitude deve ter no máximo 255 caracteres.',
            'ativo.required' => 'O campo ativo é obrigatório.',
            'ativo.string' => 'O campo ativo deve ser uma string.',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere.',
            'checkin.required' => 'O campo checkin é obrigatório.',
            'checkin.string' => 'O campo checkin deve ser uma string.',
            'checkin.max' => 'O campo checkin deve ter no máximo 1 caractere.',
            'checkout.required' => 'O campo checkout é obrigatório.',
            'checkout.string' => 'O campo checkout deve ser uma string.',
            'checkout.max' => 'O campo checkout deve ter no máximo 1 caractere.',
        ];
    }
}
