<?php

namespace App\Http\Requests\CupomDesconto;

use Illuminate\Foundation\Http\FormRequest;

class StoreCupomDescontoRequest extends FormRequest
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
            'ativo' => 'required|string|max:1',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O campo nome é obrigatório',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
            'ativo.required' => 'O campo ativo é obrigatório',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere',
        ];
    }
}
