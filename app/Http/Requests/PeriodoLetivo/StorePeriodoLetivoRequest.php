<?php

namespace App\Http\Requests\PeriodoLetivo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePeriodoLetivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'ano_letivo_id' => [
                'required', 'integer',
                Rule::exists('anos_letivos', 'id')->where('empresa_id', $empresaId),
            ],
            'nome' => ['required', 'string', 'max:40'],
            'ordem' => [
                'required', 'integer', 'min:1', 'max:20',
                Rule::unique('periodos_letivos', 'ordem')
                    ->where('ano_letivo_id', $this->input('ano_letivo_id'))
                    ->ignore($this->route('periodo_letivo') ?: 0),
            ],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
