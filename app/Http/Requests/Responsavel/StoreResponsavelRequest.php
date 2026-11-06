<?php

namespace App\Http\Requests\Responsavel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResponsavelRequest extends FormRequest
{
    /** Campos que pertencem a entidade Pessoa (dados pessoais). */
    public const PESSOA_FIELDS = [
        'nome', 'nome_social', 'cpf', 'rg', 'data_nascimento', 'sexo',
        'email', 'telefone', 'celular', 'cep', 'endereco', 'cidade', 'estado',
    ];

    /** Campos que pertencem a entidade Responsavel. */
    public const RESPONSAVEL_FIELDS = [
        'profissao', 'local_trabalho', 'observacoes', 'ativo', 'user_id',
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

            // Dados do responsavel
            'profissao' => ['nullable', 'string', 'max:120'],
            'local_trabalho' => ['nullable', 'string', 'max:160'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('empresa_id', $empresaId)],
        ];
    }
}
