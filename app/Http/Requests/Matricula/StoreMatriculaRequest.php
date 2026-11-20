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

    /**
     * So uma modalidade de bolsa fica ativa: a outra e zerada.
     * Sem tipo informado, infere pelo campo preenchido; bolsa zerada vira nula.
     */
    protected function prepareForValidation(): void
    {
        $tipo = $this->input('bolsa_tipo');

        if (! array_key_exists($tipo, Matricula::BOLSA_TIPOS)) {
            $tipo = (float) $this->input('bolsa_valor', 0) > 0 ? 'valor'
                : ((float) $this->input('bolsa_percentual', 0) > 0 ? 'percentual' : null);
        }

        $percentual = $tipo === 'percentual' ? (float) $this->input('bolsa_percentual', 0) : 0;
        $valor = $tipo === 'valor' ? (float) $this->input('bolsa_valor', 0) : 0;

        if ($tipo !== null && $percentual <= 0 && $valor <= 0) {
            $tipo = null;
        }

        $this->merge([
            'bolsa_tipo' => $tipo,
            'bolsa_percentual' => $tipo === 'percentual' ? $percentual : null,
            'bolsa_valor' => $tipo === 'valor' ? $valor : null,
        ]);
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
            'bolsa_tipo' => ['nullable', Rule::in(array_keys(Matricula::BOLSA_TIPOS))],
            'bolsa_percentual' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bolsa_valor' => ['nullable', 'numeric', 'min:0'],
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
