<?php

namespace App\Http\Requests\Tarefa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTarefaRequest extends FormRequest
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
                'nullable', 'integer',
                Rule::exists('disciplinas', 'id')->where('empresa_id', $empresaId),
            ],
            'disciplina_ids' => ['nullable', 'array', 'max:30'],
            'disciplina_ids.*' => [
                'integer',
                Rule::exists('disciplinas', 'id')->where('empresa_id', $empresaId),
            ],
            'titulo' => ['required', 'string', 'max:200'],
            'tipo' => ['nullable', Rule::in(array_keys(\App\Models\Tarefa::TIPOS))],
            'descricao' => ['nullable', 'string', 'max:4000'],
            'instrucoes' => ['nullable', 'string', 'max:4000'],
            'data_entrega' => ['required', 'date'],
            'nota_maxima' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tipo_entrega' => ['nullable', Rule::in(array_keys(\App\Models\Tarefa::TIPOS_ENTREGA))],
            'gerar_entregas' => ['nullable', 'boolean'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
            'questoes' => ['nullable', 'array', 'max:50'],
            'questoes.*.tipo' => ['required', Rule::in(['objetiva', 'discursiva'])],
            'questoes.*.enunciado' => ['required', 'string', 'max:2000'],
            'questoes.*.alternativas' => ['nullable', 'array', 'max:5'],
            'questoes.*.alternativas.*' => ['nullable', 'string', 'max:500'],
            'questoes.*.gabarito' => ['nullable', 'string', 'max:10'],
            'questoes.*.valor' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
