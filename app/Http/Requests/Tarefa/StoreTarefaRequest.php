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
            'titulo' => ['required', 'string', 'max:200'],
            'descricao' => ['nullable', 'string', 'max:4000'],
            'instrucoes' => ['nullable', 'string', 'max:4000'],
            'data_entrega' => ['required', 'date'],
            'nota_maxima' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'gerar_entregas' => ['nullable', 'boolean'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
