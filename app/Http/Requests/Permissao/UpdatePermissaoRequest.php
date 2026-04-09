<?php

namespace App\Http\Requests\Permissao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissaoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($this->route('permissao'))],
            'guard_name' => 'required|string|max:255',
            'descricao'  => 'nullable|string|max:500',
            'grupo'      => 'nullable|string|max:80',
        ];
    }

    public function messages()
    {
        return [
            'name.required'       => 'O campo nome é obrigatório',
            'name.unique'         => 'Já existe uma permissão com este nome',
            'guard_name.required' => 'O guard é obrigatório',
        ];
    }
}
