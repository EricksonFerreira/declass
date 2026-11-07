<?php

namespace App\Http\Requests\Diario;

use App\Models\Frequencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Chamada unificada: dados da aula (conteudo/observacoes) + presenca
 * dos alunos, gravados juntos para (turma, disciplina, data).
 */
class SalvarChamadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'turma_id' => [
                'required', 'integer',
                Rule::exists('turmas', 'id')->where('empresa_id', $empresaId),
            ],
            'disciplina_id' => [
                'required', 'integer',
                Rule::exists('disciplinas', 'id')->where('empresa_id', $empresaId),
            ],
            'data' => ['required', 'date'],
            'conteudo' => ['required', 'string', 'max:10000'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'frequencias' => ['nullable', 'array'],
            'frequencias.*.matricula_id' => [
                'required', 'integer',
                Rule::exists('matriculas', 'id')->where('empresa_id', $empresaId),
            ],
            'frequencias.*.status' => ['required', Rule::in(array_keys(Frequencia::STATUS))],
            'frequencias.*.justificativa' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
