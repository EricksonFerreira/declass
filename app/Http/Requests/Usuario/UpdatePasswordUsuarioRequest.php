<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordUsuarioRequest extends FormRequest
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
            'password' => [
                'required',
                'string',
                'max:20',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
            'confirmacao_senha' => 'required|string|max:20|same:password',
    ];
    }
    public function messages()
    {
        return [
            'password.required' => 'O campo senha é obrigatório',
            'password.max' => 'O campo senha deve ter no máximo 20 caracteres',
            'password.regex' => 'A senha deve conter pelo menos uma letra minúscula, uma letra maiúscula, um número e um caractere especial.',
            'confirmacao_senha.required' => 'O campo confirmação de senha é obrigatório',
            'confirmacao_senha.max' => 'O campo confirmação de senha deve ter no máximo 20 caracteres',
            'confirmacao_senha.same' => 'A confirmação da senha não corresponde à senha.',
        ];
    }
}
