<?php

namespace App\Http\Requests\Dano;

use App\Support\EmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreDanoRequest extends FormRequest
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

        return [
            'nome' => [
                'required', 'string', 'max:255',
                Rule::unique('danos', 'nome')->where('empresa_id', $empresaId),
            ],
            'ativo' => 'required|string|max:1',
            'localizacao' => 'required|string|in:interior,exterior',
            'categoria_id' => 'required|integer|exists:categoria_danos,id',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O campo nome é obrigatório',
            'nome.unique' => 'Já existe um dano com este nome nesta empresa.',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
            'ativo.required' => 'O campo ativo é obrigatório',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere',
            'localizacao.required' => 'O campo localizacao é obrigatório',
            'localizacao.in' => 'O campo localizacao deve ser interior ou exterior',
            'categoria_id.required' => 'O campo categoria_id é obrigatório',
            'categoria_id.integer' => 'O campo categoria_id deve ser um número inteiro',
            'categoria_id.exists' => 'O campo categoria_id deve existir na tabela categoria_danos',
        ];
    }
}
