<?php

namespace App\Http\Requests\SolicitacaoMatricula;

use App\Models\SolicitacaoMatricula;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSolicitacaoMatriculaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'escola_id' => [
                'required', 'integer',
                Rule::exists('escolas', 'id')->where('empresa_id', $empresaId),
            ],
            'ano_letivo_id' => [
                'nullable', 'integer',
                Rule::exists('anos_letivos', 'id')->where('empresa_id', $empresaId),
            ],
            'etapa_id' => [
                'nullable', 'integer',
                Rule::exists('etapas', 'id')->where('empresa_id', $empresaId),
            ],
            'turma_id' => [
                'nullable', 'integer',
                Rule::exists('turmas', 'id')->where('empresa_id', $empresaId),
            ],
            'nome_aluno' => ['required', 'string', 'max:255'],
            'data_nascimento_aluno' => ['nullable', 'date', 'before_or_equal:today'],
            'sexo_aluno' => ['nullable', 'string', Rule::in(['masculino', 'feminino', 'outro'])],
            'nome_responsavel' => ['required', 'string', 'max:255'],
            'parentesco' => ['nullable', 'string', Rule::in(array_keys(SolicitacaoMatricula::PARENTESCOS))],
            'telefone_responsavel' => ['nullable', 'string', 'max:30'],
            'email_responsavel' => ['nullable', 'email', 'max:190'],
            'cpf_responsavel' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', Rule::in(array_keys(SolicitacaoMatricula::STATUS))],
            'observacoes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
