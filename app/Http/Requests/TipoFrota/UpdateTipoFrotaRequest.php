<?php

namespace App\Http\Requests\TipoFrota;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTipoFrotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255|unique:tipo_frota,nome,' . $this->route('tipo_frotum'),
            'ativo' => 'required|string|max:1',
            'caracteristicas' => 'required|array',
            'caracteristicas.*' => 'integer|exists:caracteristicas_frotas,id',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O campo nome deve ser uma string.',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            'nome.unique' => 'O nome informado já está em uso.',
            'ativo.required' => 'O campo ativo é obrigatório.',
            'ativo.string' => 'O campo ativo deve ser uma string.',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere.',
            'caracteristicas.required' => 'O campo características é obrigatório.',
            'caracteristicas.array' => 'O campo características deve ser um array.',
            'caracteristicas.*.integer' => 'O campo características deve ser um inteiro.',
            'caracteristicas.*.exists' => 'A característica informada não existe.',
        ];
    }
}
