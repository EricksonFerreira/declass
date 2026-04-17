<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordUsuarioRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'resetar_para_acesso' => $this->boolean('resetar_para_acesso'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isResetPadrao = $this->boolean('resetar_para_acesso');

        $passwordRules = $isResetPadrao
            ? ['required', 'string', 'in:Senh@123']
            : ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()];

        return [
            'resetar_para_acesso' => ['nullable', 'boolean'],
            'password' => $passwordRules,
            'confirmacao_senha' => ['required', 'string', 'same:password'],
        ];
    }

    public function messages(): array
    {
        return [
            'resetar_para_acesso.boolean' => 'O indicador de reset de acesso deve ser verdadeiro ou falso.',
            'password.required' => 'O campo senha e obrigatorio',
            'password.in' => 'A senha padrao de acesso deve ser Senh@123.',
            'confirmacao_senha.required' => 'O campo confirmacao de senha e obrigatorio',
            'confirmacao_senha.same' => 'A confirmacao da senha nao corresponde a senha.',
        ];
    }
}
