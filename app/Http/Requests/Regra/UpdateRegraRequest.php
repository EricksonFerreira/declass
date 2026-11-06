<?php

namespace App\Http\Requests\Regra;

use App\Support\EmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRegraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? auth()->user()?->empresa_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->ignore($this->route('regra') ?? $this->route('tipo_usuario'))
                    ->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
            'guard_name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => [
                Rule::exists('permissions', 'id'),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (auth()->user()?->isSuperAdmin()) {
                return;
            }

            $roleName = mb_strtolower(trim((string) $this->input('name')));
            if (in_array($roleName, ['superadmin'], true)) {
                $validator->errors()->add('name', 'Esse nome de perfil e reservado pelo sistema.');
            }
        });
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
