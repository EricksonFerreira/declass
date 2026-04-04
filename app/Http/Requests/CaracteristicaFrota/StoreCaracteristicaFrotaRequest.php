<?php

namespace App\Http\Requests\CaracteristicaFrota;

use App\Support\EmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCaracteristicaFrotaRequest extends FormRequest
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
        $empresaId = app(EmpresaContext::class)->empresaId();
        $uniqueNome = Rule::unique('caracteristicas_frotas', 'nome');
        if ($empresaId !== null) {
            $uniqueNome = $uniqueNome->where(fn ($q) => $q->where('empresa_id', $empresaId));
        }

        return [
            'nome' => ['required', 'string', 'max:255', $uniqueNome],
            'tipo_valor_caracteristica_frota_id' => 'required|integer|exists:tipo_valor_caracteristica_frota,id',
            'descricao' => 'nullable|string|max:1500',
            'ativo' => 'required|string|max:1',
            'imagem' => 'nullable|image|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O campo nome é obrigatório',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
            'nome.unique' => 'O nome informado já está em uso.',
            'tipo_valor_caracteristica_frota_id.required' => 'O campo tipo de valor é obrigatório',
            'tipo_valor_caracteristica_frota_id.integer' => 'O campo tipo de valor deve ser um inteiro',
            'tipo_valor_caracteristica_frota_id.exists' => 'O tipo de valor informado não existe',
            'descricao.max' => 'O campo descrição deve ter no máximo 1500 caracteres',
            'ativo.required' => 'O campo ativo é obrigatório',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere',
            'imagem.image' => 'A imagem enviada deve ser um arquivo de imagem v\u00e1lido.',
            'imagem.max' => 'A imagem deve ter no m\u00e1ximo 2048 kilobytes.',
        ];
    }
}
