<?php

namespace App\Http\Requests\CaracteristicaFrota;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaracteristicaFrotaRequest extends FormRequest
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
            'codigo' => 'required|string|max:255',
            'nome' => 'required|string|max:255|unique:caracteristicas_frotas,nome,' . $this->route('caracteristica_frotum'),
            'tipo_valor_caracteristica_frota_id' => 'required|integer|exists:tipo_valor_caracteristica_frota,id',
            'descricao' => 'nullable|string|max:1500',
            'ativo' => 'required|string|max:1',
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'O campo código é obrigatório.',
            'codigo.string' => 'O campo código deve ser uma string.',
            'codigo.max' => 'O campo código deve ter no máximo 255 caracteres.',
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O campo nome deve ser uma string.',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            'nome.unique' => 'O nome informado já está em uso.',
            'tipo_valor_caracteristica_frota_id.required' => 'O campo tipo de valor é obrigatório.',
            'tipo_valor_caracteristica_frota_id.integer' => 'O campo tipo de valor deve ser um inteiro.',
            'tipo_valor_caracteristica_frota_id.exists' => 'O tipo de valor informado não existe.',
            'descricao.string' => 'O campo descrição deve ser uma string.',
            'descricao.max' => 'O campo descrição deve ter no máximo 1500 caracteres.',
            'ativo.required' => 'O campo ativo é obrigatório.',
            'ativo.string' => 'O campo ativo deve ser uma string.',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere.',
        ];
    }
}
