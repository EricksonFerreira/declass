<?php

namespace App\Http\Requests\TipoChecklist;

use App\Support\EmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTipoChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? Auth::user()?->empresa_id;
        $uniqueNome = Rule::unique('tipo_checklist', 'nome');

        if ($empresaId !== null) {
            $uniqueNome = $uniqueNome->where(fn ($query) => $query->where('empresa_id', $empresaId));
        }

        return [
            'nome' => ['required', 'string', 'max:255', $uniqueNome],
            'ativo' => 'required|string|max:1',
            'item_checklist_id' => 'required|array|min:1',
            'item_checklist_id.*' => 'integer|exists:item_checklist,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.unique' => 'Já existe um tipo de checklist com este nome nesta empresa.',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            'ativo.required' => 'O campo ativo é obrigatório.',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere.',
            'item_checklist_id.required' => 'Selecione ao menos um item de checklist.',
            'item_checklist_id.array' => 'Os itens de checklist devem ser enviados em lista.',
            'item_checklist_id.min' => 'Selecione ao menos um item de checklist.',
            'item_checklist_id.*.exists' => 'Um dos itens de checklist é inválido.',
        ];
    }
}
