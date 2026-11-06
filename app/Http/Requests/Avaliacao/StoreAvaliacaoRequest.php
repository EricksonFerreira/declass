<?php

namespace App\Http\Requests\Avaliacao;

use App\Models\Avaliacao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAvaliacaoRequest extends FormRequest
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
            'tipo' => ['required', Rule::in(array_keys(Avaliacao::TIPOS))],
            'nome' => ['required', 'string', 'max:150'],
            'periodo' => ['nullable', 'string', 'max:30'],
            'data' => ['nullable', 'date'],
            'nota_maxima' => ['required', 'numeric', 'min:0.1', 'max:1000'],
            'descricao' => ['nullable', 'string', 'max:10000'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
