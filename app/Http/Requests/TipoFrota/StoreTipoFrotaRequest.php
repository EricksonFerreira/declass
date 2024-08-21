<?php

namespace App\Http\Requests\TipoFrota;

use Illuminate\Foundation\Http\FormRequest;

class StoreTipoFrotaRequest extends FormRequest
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
            'codigo'=> 'required|string|max:255',
            'nome' => 'required|string|max:255',
            'ativo' => 'required|string|max:1',
            'caracteristicas' => 'required|array',
            'caracteristicas.*' => 'integer|exists:caracteristicas_frotas,id',
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'O campo código é obrigatório',
            'codigo.string' => 'O campo código deve ser uma string.',
            'codigo.max' => 'O campo código deve ter no máximo 255 caracteres',
            'nome.required' => 'O campo nome é obrigatório',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
            'ativo.required' => 'O campo ativo é obrigatório',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere',
            'caracteristicas.required' => 'O campo características é obrigatório',
            'caracteristicas.array' => 'O campo características deve ser um array',
            'caracteristicas.*.integer' => 'O campo características deve ser um inteiro',
            'caracteristicas.*.exists' => 'A característica informada não existe',
        ];
    }
}
