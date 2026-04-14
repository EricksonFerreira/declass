<?php

namespace App\Http\Requests\CupomDesconto;

use App\Support\EmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreCupomDescontoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? Auth::user()?->empresa_id;
        $uniqueNome = Rule::unique('cupom_desconto', 'nome');

        if ($empresaId !== null) {
            $uniqueNome = $uniqueNome->where(fn ($query) => $query->where('empresa_id', $empresaId));
        }

        return [
            'nome' => ['required', 'string', 'max:255', $uniqueNome],
            'ativo' => 'required|string|max:1',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.unique' => 'Já existe um cupom de desconto com este nome nesta empresa.',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            'ativo.required' => 'O campo ativo é obrigatório.',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere.',
        ];
    }
}
