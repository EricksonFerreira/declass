<?php

namespace App\Http\Requests\Aluno;

use App\Models\Aluno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlunoRequest extends FormRequest
{
    /** Campos que pertencem a entidade Pessoa (dados pessoais). */
    public const PESSOA_FIELDS = [
        'nome', 'nome_social', 'cpf', 'rg', 'data_nascimento', 'sexo',
        'email', 'telefone', 'celular', 'cep', 'endereco', 'cidade', 'estado',
    ];

    /** Campos que pertencem a entidade Aluno (dados escolares). */
    public const ALUNO_FIELDS = [
        'registro_academico', 'escola_id', 'data_ingresso', 'turno_preferencial',
        'necessidades_especiais', 'informacoes_saude', 'contatos_emergencia',
        'restricoes_acesso', 'observacoes', 'ativo', 'user_id',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            // Dados pessoais (pessoas)
            'nome' => ['required', 'string', 'max:255'],
            'nome_social' => ['nullable', 'string', 'max:255'],
            'cpf' => [
                'nullable', 'string', 'max:20',
                Rule::unique('pessoas', 'cpf')->where('empresa_id', $empresaId),
            ],
            'rg' => ['nullable', 'string', 'max:20'],
            'data_nascimento' => ['nullable', 'date'],
            'sexo' => ['nullable', Rule::in(array_keys(\App\Models\Pessoa::SEXOS))],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'celular' => ['nullable', 'string', 'max:30'],
            'cep' => ['nullable', 'string', 'max:10'],
            'endereco' => ['nullable', 'string', 'max:500'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'size:2'],

            // Dados escolares (alunos)
            'registro_academico' => ['nullable', 'string', 'max:30'],
            'escola_id' => ['nullable', 'integer', Rule::exists('escolas', 'id')->where('empresa_id', $empresaId)],
            'data_ingresso' => ['nullable', 'date'],
            'turno_preferencial' => ['nullable', Rule::in(array_keys(Aluno::TURNOS))],
            'necessidades_especiais' => ['nullable', 'string', 'max:5000'],
            'informacoes_saude' => ['nullable', 'string', 'max:5000'],
            'contatos_emergencia' => ['nullable', 'string', 'max:5000'],
            'restricoes_acesso' => ['nullable', 'string', 'max:5000'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('empresa_id', $empresaId)],
        ];
    }
}
