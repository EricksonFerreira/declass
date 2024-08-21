<?php

namespace App\Http\Requests\GrupoFrota;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGrupoFrotaRequest extends FormRequest
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
            'codigo' => 'required|string|max:50',
            'nome' => 'required|string|max:255|unique:grupos_frota,nome,' . $this->route('grupo_frotum'),
            'codigo_sipp' => 'required|string|max:50',
            'tipo_frota_id' => 'required|integer|exists:tipo_frota,id',
            'ativo' => 'required|string|max:1',
            'marca_id' => 'required|integer|exists:marcas,id',
            'imagem' => 'nullable|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'O campo código é obrigatório.',
            'codigo.string' => 'O campo código deve ser uma string.',
            'codigo.max' => 'O campo código deve ter no máximo 50 caracteres.',
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O campo nome deve ser uma string.',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            'nome.unique' => 'O nome informado já está em uso.',
            'codigo_sipp.required' => 'O campo código SIPP é obrigatório.',
            'codigo_sipp.string' => 'O campo código SIPP deve ser uma string.',
            'codigo_sipp.max' => 'O campo código SIPP deve ter no máximo 50 caracteres.',
            'tipo_frota_id.required' => 'O campo tipo de frota é obrigatório.',
            'tipo_frota_id.integer' => 'O campo tipo de frota deve ser um inteiro.',
            'tipo_frota_id.exists' => 'O tipo de frota informado não existe.',
            'ativo.required' => 'O campo ativo é obrigatório.',
            'ativo.string' => 'O campo ativo deve ser uma string.',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere.',
            'marca_id.required' => 'O campo marca é obrigatório.',
            'marca_id.integer' => 'O campo marca deve ser um inteiro.',
            'marca_id.exists' => 'A marca informada não existe.',
            'imagem.max' => 'O arquivo deve ter no máximo 2048 kilobytes.',
        ];
    }
}
