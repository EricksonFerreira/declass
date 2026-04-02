<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

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
            ? ['required', 'string', 'in:car@12345']
            : ['required', 'string', 'max:20', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'];

        return [
            'resetar_para_acesso' => ['nullable', 'boolean'],
            'password' => $passwordRules,
            'confirmacao_senha' => ['required', 'string', 'max:20', 'same:password'],
        ];
    }

    public function messages(): array
    {
        return [
            'resetar_para_acesso.boolean' => 'O indicador de reset de acesso deve ser verdadeiro ou falso.',
            'password.required' => 'O campo senha e obrigatorio',
            'password.max' => 'O campo senha deve ter no maximo 20 caracteres',
            'password.regex' => 'A senha deve conter pelo menos uma letra minuscula, uma letra maiuscula, um numero e um caractere especial.',
            'password.in' => 'A senha padrao de acesso deve ser car@12345.',
            'confirmacao_senha.required' => 'O campo confirmacao de senha e obrigatorio',
            'confirmacao_senha.max' => 'O campo confirmacao de senha deve ter no maximo 20 caracteres',
            'confirmacao_senha.same' => 'A confirmacao da senha nao corresponde a senha.',
        ];
    }
}
