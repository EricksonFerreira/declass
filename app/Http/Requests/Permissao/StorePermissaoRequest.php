<?php

namespace App\Http\Requests\Permissao;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissaoRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:permissions,name,' . $this->route('permissao'),
            'guard_name' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'O campo nome é obrigatório',
            'name.max' => 'O campo nome deve ter no máximo 255 caracteres',
            'name.unique' => 'O nome informado já está em uso',
            'guard_name.required' => 'O campo de política de segurança é obrigatório',
            'guard_name.max' => 'O campo de política de segurança deve ter no máximo 255 caracteres',
        ];
    }
}
