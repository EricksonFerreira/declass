<?php

namespace App\Http\Requests\Regra;

use App\Support\EmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $empresaId = app(EmpresaContext::class)->empresaId() ?? auth()->user()?->empresa_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
            'guard_name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => [
                Rule::exists('permissions', 'id'),
            ],
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
