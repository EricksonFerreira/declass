<?php

namespace App\Http\Requests\Modelo;

use App\Support\EmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreModeloRequest extends FormRequest
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
        $empresaId = Auth::user()?->empresa_id ?? app(EmpresaContext::class)->empresaId();
        $marcaExists = Rule::exists('marcas', 'id');

        if ($empresaId !== null) {
            $marcaExists = $marcaExists->where(fn ($query) => $query->where('empresa_id', (int) $empresaId));
        }

        return [
            'nome' => 'required|string|max:255',
            'marca_id' => ['required', 'integer', $marcaExists],
            'segmento_id' => 'required|integer|exists:segmentos,id',
            'ativo' => 'required|string|max:1',
            'imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O campo nome é obrigatório',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
            'marca_id.required' => 'O campo marca é obrigatório',
            'marca_id.integer' => 'O campo marca deve ser um inteiro',
            'marca_id.exists' => 'A marca informada não existe',
            'segmento_id.required' => 'O campo segmento é obrigatório',
            'segmento_id.integer' => 'O campo segmento deve ser um inteiro',
            'segmento_id.exists' => 'O segmento informado não existe',
            'ativo.required' => 'O campo ativo é obrigatório',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere',
            'imagem.image' => 'O arquivo enviado deve ser uma imagem.',
            'imagem.mimes' => 'A imagem deve ser do tipo: jpg, jpeg, png, svg ou webp.',
            'imagem.max' => 'O arquivo deve ter no maximo 2048 kilobytes.',
        ];
    }
}
