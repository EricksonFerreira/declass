<?php

namespace App\Http\Requests\CategoriaDano;

use App\Support\EmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCategoriaDanoRequest extends FormRequest
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
        $empresaId = app(EmpresaContext::class)->empresaId() ?? Auth::user()?->empresa_id;

        return [
            'nome' => [
                'required', 'string', 'max:255',
                Rule::unique('categoria_danos', 'nome')
                    ->ignore($this->route('categoria_dano'))
                    ->where('empresa_id', $empresaId),
            ],
            'ativo' => 'required|string|max:1',
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
        ];
    }
}
