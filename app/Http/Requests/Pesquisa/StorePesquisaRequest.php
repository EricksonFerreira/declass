<?php

namespace App\Http\Requests\Pesquisa;

use App\Models\Pesquisa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePesquisaRequest extends FormRequest
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
                'nullable', 'integer',
                Rule::exists('escolas', 'id')->where('empresa_id', $empresaId),
            ],
            'titulo' => ['required', 'string', 'max:190'],
            'descricao' => ['nullable', 'string', 'max:4000'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'status' => ['required', 'string', Rule::in(array_keys(Pesquisa::STATUS))],
            'perguntas' => ['required', 'array', 'min:1'],
            'perguntas.*.pergunta' => ['required', 'string', 'max:500'],
            'perguntas.*.tipo' => ['required', 'string', Rule::in(array_keys(\App\Models\PesquisaPergunta::TIPOS))],
            'perguntas.*.obrigatoria' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
