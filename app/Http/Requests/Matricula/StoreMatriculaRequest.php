<?php

namespace App\Http\Requests\Matricula;

use App\Models\Matricula;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMatriculaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'aluno_id' => [
                'required', 'integer',
                Rule::exists('alunos', 'id')->where('empresa_id', $empresaId),
            ],
            'turma_id' => [
                'required', 'integer',
                Rule::exists('turmas', 'id')->where('empresa_id', $empresaId),
            ],
            'status' => ['nullable', Rule::in(array_keys(Matricula::STATUS))],
            'plano_mensalidade_id' => [
                'nullable', 'integer',
                Rule::exists('planos_mensalidade', 'id')->where('empresa_id', $empresaId),
            ],
            'numero_chamada' => [
                'nullable', 'integer', 'min:1', 'max:999',
                Rule::unique('matriculas', 'numero_chamada')
                    ->where('turma_id', $this->input('turma_id')),
            ],
            'data_matricula' => ['nullable', 'date'],
            'data_saida' => ['nullable', 'date', 'after_or_equal:data_matricula'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
