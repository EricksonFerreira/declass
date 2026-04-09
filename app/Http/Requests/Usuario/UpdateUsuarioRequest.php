<?php

namespace App\Http\Requests\Usuario;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
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
                Rule::unique('users', 'email')->ignore($this->route('usuario'))->where(fn ($query) => $query->where('empresa_id', $empresaId)),
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
            'numero_identificacao' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'numero_identificacao')->ignore($this->route('usuario'))->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
            'role_id' => 'required|integer|exists:roles,id',
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
            'integer' => 'O campo :attribute deve ser numerico.',
            'exists' => 'O valor informado para :attribute nao existe.',
            'array' => 'O campo :attribute deve ser uma lista.',
            'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
            'email.unique' => 'O email informado ja esta em uso.',
            'numero_identificacao.unique' => 'O numero de identificacao informado ja esta em uso.',
            'celular.regex' => 'O celular deve estar no formato (XX) XXXXX-XXXX.',
            'estacoes.array' => 'As estacoes permitidas devem ser informadas em lista.',
            'estacoes.*.integer' => 'Cada estacao permitida deve ser numerica.',
            'estacoes.*.exists' => 'Uma das estacoes selecionadas nao existe.',
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
            'zip_code' => 'zip code',
            'cidade' => 'cidade',
            'endereco' => 'endereco',
            'numero_identificacao' => 'numero de identificacao',
            'role_id' => 'regra de usuario',
            'numero_carta_conducao' => 'numero da carta de conducao',
            'local_emissao_carta_conducao' => 'local de emissao da carta de conducao',
            'pais_emissao_carta_conducao' => 'pais de emissao da carta de conducao',
            'data_emissao_carta_conducao' => 'data de emissao da carta de conducao',
            'data_validade_carta_conducao' => 'data de validade da carta de conducao',
            'acesso_todas_estacoes' => 'acesso a todas as estacoes',
            'estacoes' => 'estacoes permitidas',
            'estacoes.*' => 'estacao permitida',
        ];
    }
}
