<?php

namespace App\Http\Requests\GrupoFrota;

use Illuminate\Foundation\Http\FormRequest;

class StoreGrupoFrotaRequest extends FormRequest
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
            'nome' => 'required|string|max:255',
            'codigo_sipp' => 'required|string|max:50',
            'tipo_frota_id' => 'required|integer|exists:tipo_frota,id',
            'valor_diario' => 'required|string',
            'ativo' => 'required|string|max:1',
            'imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'imagens' => 'nullable|array',
            'imagens.*' => 'image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'O campo código é obrigatório',
            'codigo.string' => 'O campo código deve ser uma string.',
            'codigo.max' => 'O campo código deve ter no máximo 50 caracteres',
            'nome.required' => 'O campo nome é obrigatório',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
            'codigo_sipp.required' => 'O campo código SIPP é obrigatório',
            'codigo_sipp.string' => 'O campo código SIPP deve ser uma string.',
            'codigo_sipp.max' => 'O campo código SIPP deve ter no máximo 50 caracteres',
            'tipo_frota_id.required' => 'O campo tipo de frota é obrigatório',
            'tipo_frota_id.integer' => 'O campo tipo de frota deve ser um inteiro',
            'tipo_frota_id.exists' => 'O tipo de frota informado não existe',
            'valor_diario.required' => 'O campo valor diário é obrigatório',
            'valor_diario.numeric' => 'O campo valor diário deve ser um número',
            'ativo.required' => 'O campo ativo é obrigatório',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere',
            'imagem.image' => 'O arquivo enviado deve ser uma imagem.',
            'imagem.mimes' => 'A imagem deve ser do tipo: jpg, jpeg, svg, png ou webp.',
            'imagem.max' => 'O arquivo deve ter no máximo 2048 kilobytes.',
            'imagens.array' => 'As imagens devem ser enviadas em formato de lista.',
            'imagens.*.image' => 'Cada arquivo deve ser uma imagem valida.',
            'imagens.*.mimes' => 'As imagens devem ser do tipo: jpg, jpeg, svg, png ou webp.',
            'imagens.*.max' => 'Cada imagem deve ter no maximo 2048 kilobytes.',
        ];
    }
}
