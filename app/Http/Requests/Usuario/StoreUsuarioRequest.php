<?php

namespace App\Http\Requests\Usuario;

use App\Support\EmpresaContext;
use App\Support\SchemaCache;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('tipo_usuario_id') && ! $this->filled('role_id')) {
            $this->merge([
                'role_id' => $this->input('tipo_usuario_id'),
            ]);
        }

        $this->merge([
            'cadastro_reserva' => $this->boolean('cadastro_reserva'),
            'usuario' => Str::lower(trim((string) $this->input('usuario', ''))) ?: null,
        ]);

        if (! $this->filled('role_id')) {
            $colaboradorRole = \App\Models\Regra::where('name', 'colaborador_administrativo')
                ->orWhere('name', 'Colaborador Administrativo')
                ->first();
            $this->merge([
                'role_id' => $colaboradorRole?->id ?? 8,
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? $this->user()?->empresa_id;
        $isCadastroReserva = $this->boolean('cadastro_reserva');

        return [
            'nome' => 'required|string|max:255',
            'email' => [
                $isCadastroReserva ? 'nullable' : 'required',
                'string',
                'max:150',
                Rule::unique('users', 'email')->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
            'usuario' => [
                'nullable',
                'string',
                'max:60',
                'regex:/^[a-z0-9._-]+$/',
                ...(SchemaCache::hasColumn('users', 'usuario')
                    ? [Rule::unique('users', 'usuario')->where(fn ($query) => $query->where('empresa_id', $empresaId))]
                    : []),
            ],
            'sexo' => 'nullable|string|max:1',
            'data_nascimento' => 'nullable|date',
            'local_nascimento' => 'nullable|string|max:100',
            'telefone' => 'nullable|string|max:20',
            'celular' => ['required', 'string', 'max:20', 'regex:/^\(\d{2}\) \d{4,5}-\d{4}$/'],
            'pais_sigla' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:20',
            'cidade' => 'nullable|string|max:100',
            'endereco' => 'nullable|string|max:255',
            'numero_identificacao' => 'nullable|string|max:20',
            'role_id' => [$isCadastroReserva ? 'nullable' : 'required', 'integer', 'exists:roles,id'],
            'funcao_id' => [
                'nullable',
                'integer',
                Rule::exists('funcoes', 'id')->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
            'jornada_diaria' => 'nullable|numeric|min:0|max:24',
            'ativo' => 'nullable|boolean',
            'password' => ['nullable', 'string', 'max:20'],
            'confirmacao_senha' => ['nullable', 'string', 'max:20'],
            'numero_carta_conducao' => 'nullable|string|max:20',
            'local_emissao_carta_conducao' => 'nullable|string|max:100',
            'pais_emissao_carta_conducao' => 'nullable|string|max:2',
            'data_emissao_carta_conducao' => 'nullable|date|before:'.Carbon::now()->format('Y-m-d'),
            'data_validade_carta_conducao' => 'nullable|date|after:data_emissao_carta_conducao',
            'cadastro_reserva' => 'nullable|boolean',
        ];
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
            'usuario.unique' => 'O usuario informado ja esta em uso.',
            'usuario.regex' => 'O usuario pode conter apenas letras minusculas, numeros, ponto, hifen e sublinhado.',
            'celular.regex' => 'O celular deve estar no formato (XX) XXXX-XXXX ou (XX) XXXXX-XXXX.',
            'cadastro_reserva.boolean' => 'O indicador de cadastro pela reserva deve ser verdadeiro ou falso.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'email' => 'e-mail',
            'usuario' => 'usuario',
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
            'funcao_id' => 'funcao',
            'jornada_diaria' => 'jornada diaria',
            'password' => 'senha',
            'confirmacao_senha' => 'confirmacao da senha',
            'numero_carta_conducao' => 'numero da carta de conducao',
            'local_emissao_carta_conducao' => 'local de emissao da carta de conducao',
            'pais_emissao_carta_conducao' => 'pais de emissao da carta de conducao',
            'data_emissao_carta_conducao' => 'data de emissao da carta de conducao',
            'data_validade_carta_conducao' => 'data de validade da carta de conducao',
            'cadastro_reserva' => 'cadastro pela reserva',
        ];
    }
}
