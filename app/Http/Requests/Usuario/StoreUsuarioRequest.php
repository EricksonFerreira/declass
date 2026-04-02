<?php

namespace App\Http\Requests\Usuario;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('tipo_usuario_id') && !$this->filled('role_id')) {
            $this->merge([
                'role_id' => $this->input('tipo_usuario_id'),
            ]);
        }

        $this->merge([
            'acesso_todas_estacoes' => $this->boolean('acesso_todas_estacoes'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = auth()->user()?->empresa_id;

        return [
            'nome' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'max:150',
                Rule::unique('users', 'email')->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
            'sexo' => 'required|string|max:1',
            'data_nascimento' => 'required|date|before:' . Carbon::now()->subYears(16)->format('Y-m-d'),
            'local_nascimento' => 'required|string|max:100',
            'telefone' => 'required|string|max:20',
            'celular' => ['required', 'string', 'max:20', 'regex:/^\(\d{2}\) \d{5}-\d{4}$/'],
            'pais_sigla' => 'required|string|max:2',
            'zip_code' => 'required|string|max:20',
            'cidade' => 'required|string|max:100',
            'endereco' => 'required|string|max:255',
            'numero_identificacao' => 'required|string|max:20',
            'role_id' => 'required|integer|exists:roles,id',
            'password' => ['required', 'string', 'max:20', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'],
            'confirmacao_senha' => 'required|string|max:20|same:password',
            'numero_carta_conducao' => 'required|string|max:20',
            'local_emissao_carta_conducao' => 'required|string|max:100',
            'pais_emissao_carta_conducao' => 'required|string|max:2',
            'data_emissao_carta_conducao' => 'required|date|before:' . Carbon::now()->format('Y-m-d'),
            'data_validade_carta_conducao' => 'date',
            'acesso_todas_estacoes' => 'nullable|boolean',
            'estacoes' => 'nullable|array',
            'estacoes.*' => 'integer|exists:estacoes,id',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'O email informado ja esta em uso.',
            'celular.regex' => 'O celular deve estar no formato (XX) XXXXX-XXXX.',
            'password.regex' => 'A senha deve ter ao menos 8 caracteres com maiuscula, minuscula, numero e simbolo.',
            'confirmacao_senha.same' => 'A confirmacao da senha nao corresponde.',
            'estacoes.array' => 'As estacoes permitidas devem ser informadas em lista.',
            'estacoes.*.integer' => 'Cada estacao permitida deve ser numerica.',
            'estacoes.*.exists' => 'Uma das estacoes selecionadas nao existe.',
        ];
    }
}
