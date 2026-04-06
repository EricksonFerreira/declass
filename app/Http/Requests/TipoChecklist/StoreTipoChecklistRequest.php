<?php

namespace App\Http\Requests$ns;

use App\Support\EmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTipoChecklistRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        \ = Auth::user()?->empresa_id ?? app(EmpresaContext::class)->empresaId();
        return [
            'nome' => ['required','string','max:255', Rule::unique('{['table']}','nome')->where('empresa_id',\)],
            'ativo' => 'required|string|max:1',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O campo nome e obrigatorio.',
            'nome.unique'   => 'Ja existe {['label']} com este nome nesta empresa.',
            'nome.max'      => 'O campo nome deve ter no maximo 255 caracteres.',
            'ativo.required'=> 'O campo ativo e obrigatorio.',
            'ativo.max'     => 'O campo ativo deve ter no maximo 1 caractere.',
        ];
    }
}
