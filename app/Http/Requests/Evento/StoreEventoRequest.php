<?php

namespace App\Http\Requests\Evento;

use App\Models\Evento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventoRequest extends FormRequest
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
            'tipo' => ['required', 'string', Rule::in(array_keys(Evento::TIPOS))],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fim' => ['nullable', 'date_format:H:i'],
            'local' => ['nullable', 'string', 'max:190'],
            'publico' => ['required', 'string', Rule::in(array_keys(Evento::PUBLICOS))],
            'status' => ['required', 'string', Rule::in(array_keys(Evento::STATUS))],
            'suspende_aulas' => ['nullable', Rule::in(['S', 'N'])],
            'turmas' => ['nullable', 'array'],
            'turmas.*' => [
                'integer',
                Rule::exists('turmas', 'id')->where('empresa_id', $empresaId),
            ],
        ];
    }
}
