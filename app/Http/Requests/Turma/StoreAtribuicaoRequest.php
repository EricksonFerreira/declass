<?php

namespace App\Http\Requests\Turma;

use App\Models\Atribuicao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAtribuicaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;
        $turma = $this->route('turma');
        $turmaId = $turma instanceof \App\Models\Turma ? $turma->id : (int) $turma;

        return [
            'user_id' => [
                'required', 'integer',
                Rule::exists('users', 'id')->where('empresa_id', $empresaId),
            ],
            'vinculo' => ['nullable', Rule::in(['todas', 'disciplinas'])],
            'disciplina_id' => [
                'nullable', 'integer',
                Rule::exists('disciplinas', 'id')->where('empresa_id', $empresaId),
            ],
            'disciplina_ids' => ['required_if:vinculo,disciplinas', 'array', 'max:100'],
            'disciplina_ids.*' => [
                'integer',
                Rule::exists('turma_disciplina', 'disciplina_id')->where('turma_id', $turmaId),
            ],
            'papel' => ['required', Rule::in(array_keys(Atribuicao::PAPEIS))],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }

    /** Vinculo "todas" (regente) ignora qualquer disciplina enviada. */
    protected function prepareForValidation(): void
    {
        if ($this->input('vinculo') === 'todas') {
            $this->merge(['disciplina_ids' => []]);
        }
    }
}
