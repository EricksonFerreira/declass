<?php

namespace App\Http\Requests\Dano;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDanoRequest extends FormRequest
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
            'nome' => 'required|string|max:255|unique:danos,nome,' . $this->route('danos'),
            'ativo' => 'required|string|max:1',
            'categoria_id' => 'required|integer|exists:categoria_danos,id',
            'localizacao' => 'required|string|in:interior,exterior',

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
            'categoria_id.required' => 'O campo categoria_id é obrigatório.',
            'categoria_id.integer' => 'O campo categoria_id deve ser um número inteiro.',
            'categoria_id.exists' => 'O campo categoria_id deve existir na tabela categoria_danos.',
            'localizacao.required' => 'O campo localizacao é obrigatório.',
            'localizacao.string' => 'O campo localizacao deve ser uma string.',
            'localizacao.in' => 'O campo localizacao deve ser interior ou exterior.',
        ];
    }
}
