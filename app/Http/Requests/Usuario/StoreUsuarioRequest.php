<?php

namespace App\Http\Requests\Usuario;

use App\Support\EmpresaContext;
use App\Support\SchemaCache;
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
            'usuario' => Str::lower(trim((string) $this->input('usuario', ''))) ?: null,
        ]);

        if (! $this->filled('role_id')) {
            $roleId = \App\Models\Regra::where('guard_name', 'web')
                ->where('empresa_id', $this->user()?->empresa_id)
                ->where('name', 'membro')
                ->value('id');
            $this->merge(['role_id' => $roleId]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? $this->user()?->empresa_id;

        return [
            'nome' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
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
            'sexo' => 'nullable|string|max:2',
            'data_nascimento' => 'nullable|date',
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'cidade' => 'nullable|string|max:100',
            'numero_identificacao' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('users', 'numero_identificacao')->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
            'cargo' => 'nullable|string|max:120',
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where('empresa_id', $empresaId)],
            'ativo' => 'nullable|boolean',
            'escolas' => 'nullable|array',
            'escolas.*' => [
                'integer',
                Rule::exists('escolas', 'id')->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
            'organizacoes' => 'nullable|array',
            'organizacoes.*' => [
                'integer',
                Rule::exists('organizacoes', 'id')->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo :attribute e obrigatorio.',
            'string' => 'O campo :attribute deve ser um texto valido.',
            'max.string' => 'O campo :attribute nao pode ter mais de :max caracteres.',
            'date' => 'O campo :attribute deve ser uma data valida.',
            'integer' => 'O campo :attribute deve ser numerico.',
            'exists' => 'O valor informado para :attribute nao existe.',
            'array' => 'O campo :attribute deve ser uma lista.',
            'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
            'email.unique' => 'O email informado ja esta em uso.',
            'usuario.unique' => 'O usuario informado ja esta em uso.',
            'usuario.regex' => 'O usuario pode conter apenas letras minusculas, numeros, ponto, hifen e sublinhado.',
            'numero_identificacao.unique' => 'O numero de identificacao informado ja esta em uso.',
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
            'telefone' => 'telefone',
            'celular' => 'celular',
            'cidade' => 'cidade',
            'numero_identificacao' => 'CPF / documento',
            'cargo' => 'cargo',
            'role_id' => 'perfil de acesso',
            'ativo' => 'ativo',
            'escolas' => 'escolas',
            'organizacoes' => 'organizações',
        ];
    }
}
