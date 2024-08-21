<?php

namespace App\Http\Requests\Usuario;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
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
            'nome' => 'required|string|max:255',
            'email' => 'required|string|max:150|unique:users,email',
            'sexo' => 'required|string|max:1',
            'data_nascimento' => 'required|date|before:' . Carbon::now()->subYears(16)->format('Y-m-d'),
            'local_nascimento' => 'required|string|max:100',
            'telefone' => 'required|string|max:20',
            'celular' => [
                'required',
                'string',
                'max:20',
                'regex:/^\(\d{2}\) \d{5}-\d{4}$/'
            ],
            'pais_sigla' => 'required|string|max:2',
            'zip_code' => 'required|string|max:20',
            'cidade' => 'required|string|max:100',
            'endereco' => 'required|string|max:255',
            'numero_identificacao' => 'required|string|max:20',
            'tipo_usuario_id' => 'required|string|max:20',
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
            'nome.required' => 'O campo nome é obrigatório',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
            'email.required' => 'O campo email é obrigatório',
            'email.max' => 'O campo email deve ter no máximo 150 caracteres',
            'email.unique' => 'O email informado já está em uso',
            'sexo.required' => 'O campo sexo é obrigatório',
            'sexo.max' => 'O campo sexo deve ter no máximo 1 caracteres',
            'data_nascimento.required' => 'O campo data_nascimento é obrigatório',
            'data_nascimento.date' => 'O campo data_nascimento deve ser uma data',
            'data_nascimento.before' => 'Você deve ter pelo menos 16 anos.',
            'local_nascimento.required' => 'O campo local_nascimento é obrigatório',
            'local_nascimento.max' => 'O campo local_nascimento deve ter no máximo 100 caracteres',
            'telefone.required' => 'O campo telefone é obrigatório',
            'telefone.max' => 'O campo telefone deve ter no máximo 20 caracteres',
            'celular.required' => 'O campo celular é obrigatório',
            'celular.max' => 'O campo celular deve ter no máximo 20 caracteres',
            'celular.regex' => 'O telefone deve estar no formato (XX) XXXXX-XXXX.',
            'pais_sigla.required' => 'O campo pais é obrigatório',
            'pais_sigla.max' => 'O campo pais deve ter no máximo 2 caracteres',
            'zip_code.required' => 'O campo zipcode é obrigatório',
            'zip_code.max' => 'O campo zipcode deve ter no máximo 20 caracteres',
            'cidade.required' => 'O campo cidade é obrigatório',
            'cidade.max' => 'O campo cidade deve ter no máximo 100 caracteres',
            'endereco.required' => 'O campo endereco é obrigatório',
            'endereco.max' => 'O campo endereco deve ter no máximo 255 caracteres',
            'numero_identificacao.required' => 'O campo numero de identificacao é obrigatório',
            'numero_identificacao.max' => 'O campo numero de identificacao deve ter no máximo 20 caracteres',
            'tipo_usuario_id.required' => 'O campo tipo de usuário é obrigatório',
            'tipo_usuario_id.max' => 'O campo tipo de usuário deve ter no máximo 20 caracteres',
            'password.required' => 'O campo senha é obrigatório',
            'password.max' => 'O campo senha deve ter no máximo 20 caracteres',
            'password.regex' => 'A senha deve conter pelo menos uma letra minúscula, uma letra maiúscula, um número e um caractere especial.',
            'confirmacao_senha.required' => 'O campo confirmação de senha é obrigatório',
            'confirmacao_senha.max' => 'O campo confirmação de senha deve ter no máximo 20 caracteres',
            'confirmacao_senha.same' => 'A confirmação da senha não corresponde à senha.',
        ];
    }
}
