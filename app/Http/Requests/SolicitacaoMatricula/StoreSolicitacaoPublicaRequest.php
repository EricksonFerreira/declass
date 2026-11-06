<?php

namespace App\Http\Requests\SolicitacaoMatricula;

use App\Models\SolicitacaoMatricula;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSolicitacaoPublicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // empresa vem dos slugs da rota — o controller resolve de novo
        $empresaId = \App\Models\Empresa::query()
            ->where('slug', $this->route('empresaSlug'))
            ->value('id');

        return [
            'ano_letivo_id' => [
                'nullable', 'integer',
                Rule::exists('anos_letivos', 'id')->where('empresa_id', $empresaId),
            ],
            'etapa_id' => [
                'nullable', 'integer',
                Rule::exists('etapas', 'id')->where('empresa_id', $empresaId),
            ],
            'nome_aluno' => ['required', 'string', 'max:255'],
            'data_nascimento_aluno' => ['nullable', 'date', 'before_or_equal:today'],
            'sexo_aluno' => ['nullable', 'string', Rule::in(['masculino', 'feminino', 'outro'])],
            'nome_responsavel' => ['required', 'string', 'max:255'],
            'parentesco' => ['nullable', 'string', Rule::in(array_keys(SolicitacaoMatricula::PARENTESCOS))],
            'telefone_responsavel' => ['nullable', 'string', 'max:30'],
            'email_responsavel' => ['nullable', 'email', 'max:190'],
            'cpf_responsavel' => ['nullable', 'string', 'max:20'],
            'observacoes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
