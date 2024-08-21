<?php

namespace App\Http\Requests\Regra;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegraRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:roles,name,' . $this->route('role'),
            'guard_name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
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
            'permissions.array' => 'O campo permissões deve ser um array.',
            'permissions.*.exists' => 'Alguma das permissões selecionadas é inválida.',
        ];
    }
}
