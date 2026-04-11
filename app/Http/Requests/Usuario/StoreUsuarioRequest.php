<?php

namespace App\Http\Requests\Usuario;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
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
            'cadastro_reserva' => $this->boolean('cadastro_reserva'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = auth()->user()?->empresa_id;
        $isCadastroReserva = $this->boolean('cadastro_reserva');

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
            'telefone' => 'nullable|string|max:20',
            'celular' => ['required', 'string', 'max:20', 'regex:/^\(\d{2}\) \d{5}-\d{4}$/'],
            'pais_sigla' => 'required|string|max:2',
            'zip_code' => 'required|string|max:20',
            'cidade' => 'required|string|max:100',
            'endereco' => 'required|string|max:255',
            'numero_identificacao' => 'required|string|max:20',
            'role_id' => [$isCadastroReserva ? 'nullable' : 'required', 'integer', 'exists:roles,id'],
            'password' => $isCadastroReserva
                ? ['nullable', 'string', 'max:20']
                : ['required', 'string', 'max:20', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'],
            'confirmacao_senha' => $isCadastroReserva
                ? ['nullable', 'string', 'max:20', 'same:password']
                : ['required', 'string', 'max:20', 'same:password'],
            'numero_carta_conducao' => 'nullable|string|max:20',
            'local_emissao_carta_conducao' => 'nullable|string|max:100',
            'pais_emissao_carta_conducao' => 'nullable|string|max:2',
            'data_emissao_carta_conducao' => 'nullable|date|before:' . Carbon::now()->format('Y-m-d'),
            'data_validade_carta_conducao' => 'nullable|date|after:data_emissao_carta_conducao',
            'cadastro_reserva' => 'nullable|boolean',
            'acesso_todas_estacoes' => 'nullable|boolean',
            'estacoes' => 'nullable|array',
            'estacoes.*' => 'integer|exists:estacoes,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $roleId = (int) ($this->input('role_id') ?? 0);
            if ($roleId <= 0) {
                return;
            }

            $roleName = (string) (DB::table('roles')->where('id', $roleId)->value('name') ?? '');
            $roleKey = mb_strtolower(trim($roleName));
            if ($roleKey === '') {
                return;
            }

            $acessoTodas = (bool) $this->boolean('acesso_todas_estacoes');
            $estacoes = $this->input('estacoes', []);
            $count = is_array($estacoes) ? count(array_unique(array_filter($estacoes))) : 0;

            if ($roleKey === 'gerente') {
                if (! $acessoTodas) {
                    $v->errors()->add('acesso_todas_estacoes', 'Para o perfil Gerente, o acesso deve ser a todas as estacoes.');
                }
                return;
            }

            if (in_array($roleKey, ['colaborador_administrativo', 'colaborador administrativo'], true)) {
                if ($acessoTodas) {
                    $v->errors()->add('acesso_todas_estacoes', 'Para Colaborador Administrativo, selecione estacoes permitidas (sem acesso total).');
                }
                if ($count !== 1) {
                    $v->errors()->add('estacoes', 'Para Colaborador Administrativo, selecione exatamente 1 estacao.');
                }
                return;
            }

            if (in_array($roleKey, ['colaborador_chefe', 'colaborador chefe'], true)) {
                if ($acessoTodas) {
                    $v->errors()->add('acesso_todas_estacoes', 'Para Colaborador Chefe, selecione estacoes permitidas (sem acesso total).');
                }
                if ($count < 1 || $count > 3) {
                    $v->errors()->add('estacoes', 'Para Colaborador Chefe, selecione de 1 a 3 estacoes.');
                }
                return;
            }

            if ($roleKey === 'cliente') {
                if ($acessoTodas) {
                    $v->errors()->add('acesso_todas_estacoes', 'Para Cliente, nao utilize acesso a todas as estacoes.');
                }
                if ($count > 0) {
                    $v->errors()->add('estacoes', 'Para Cliente, nao selecione estacoes.');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'required' => 'O campo :attribute e obrigatorio.',
            'string' => 'O campo :attribute deve ser um texto valido.',
            'max.string' => 'O campo :attribute nao pode ter mais de :max caracteres.',
            'date' => 'O campo :attribute deve ser uma data valida.',
            'before' => 'O campo :attribute deve ser uma data anterior a :date.',
            'after' => 'A data de validade deve ser posterior à data de emissão.',
            'integer' => 'O campo :attribute deve ser numerico.',
            'exists' => 'O valor informado para :attribute nao existe.',
            'array' => 'O campo :attribute deve ser uma lista.',
            'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
            'same' => 'O campo :attribute deve ser igual a :other.',
            'email.unique' => 'O email informado ja esta em uso.',
            'celular.regex' => 'O celular deve estar no formato (XX) XXXXX-XXXX.',
            'password.regex' => 'A senha deve ter ao menos 8 caracteres com maiuscula, minuscula, numero e simbolo.',
            'confirmacao_senha.same' => 'A confirmacao da senha nao corresponde.',
            'estacoes.array' => 'As estacoes permitidas devem ser informadas em lista.',
            'estacoes.*.integer' => 'Cada estacao permitida deve ser numerica.',
            'estacoes.*.exists' => 'Uma das estacoes selecionadas nao existe.',
            'cadastro_reserva.boolean' => 'O indicador de cadastro pela reserva deve ser verdadeiro ou falso.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'email' => 'e-mail',
            'sexo' => 'sexo',
            'data_nascimento' => 'data de nascimento',
            'local_nascimento' => 'local de nascimento',
            'telefone' => 'telefone',
            'celular' => 'celular',
            'pais_sigla' => 'pais',
            'zip_code' => 'CPF',
            'cidade' => 'cidade',
            'endereco' => 'endereco',
            'numero_identificacao' => 'numero de identificacao',
            'role_id' => 'regra de usuario',
            'password' => 'senha',
            'confirmacao_senha' => 'confirmacao da senha',
            'numero_carta_conducao' => 'numero da carta de conducao',
            'local_emissao_carta_conducao' => 'local de emissao da carta de conducao',
            'pais_emissao_carta_conducao' => 'pais de emissao da carta de conducao',
            'data_emissao_carta_conducao' => 'data de emissao da carta de conducao',
            'data_validade_carta_conducao' => 'data de validade da carta de conducao',
            'cadastro_reserva' => 'cadastro pela reserva',
            'acesso_todas_estacoes' => 'acesso a todas as estacoes',
            'estacoes' => 'estacoes permitidas',
            'estacoes.*' => 'estacao permitida',
        ];
    }
}
