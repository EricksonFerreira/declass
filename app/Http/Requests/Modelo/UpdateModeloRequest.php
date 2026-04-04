<?php

namespace App\Http\Requests\Modelo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModeloRequest extends FormRequest
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
            'nome' => 'required|string|max:255|unique:tipo_transacoes,nome,' . $this->route('modelo'),
            'marca_id' => 'required|integer|exists:marcas,id',
            'segmento_id' => 'required|integer|exists:segmentos,id',
            'ativo' => 'required|string|max:1',
            'imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O campo nome deve ser uma string.',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            'nome.unique' => 'O nome informado já está em uso.',
            'marca_id.required' => 'O campo marca é obrigatório.',
            'marca_id.integer' => 'O campo marca deve ser um inteiro.',
            'marca_id.exists' => 'A marca informada não existe.',
            'segmento_id.required' => 'O campo segmento é obrigatório.',
            'segmento_id.integer' => 'O campo segmento deve ser um inteiro.',
            'segmento_id.exists' => 'O segmento informado não existe.',
            'ativo.required' => 'O campo ativo é obrigatório.',
            'ativo.string' => 'O campo ativo deve ser uma string.',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere.',
            'imagem.image' => 'O arquivo enviado deve ser uma imagem.',
            'imagem.mimes' => 'A imagem deve ser do tipo: jpg, jpeg, png, svg ou webp.',
            'imagem.max' => 'O arquivo deve ter no maximo 2048 kilobytes.',
        ];
    }
}
