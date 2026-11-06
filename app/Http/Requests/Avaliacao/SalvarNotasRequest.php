<?php

namespace App\Http\Requests\Avaliacao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Lancamento de notas em lote para uma avaliacao.
 * nota NULL/ausente = nao lancada (limpa registro existente).
 */
class SalvarNotasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'notas' => ['present', 'array'],
            'notas.*.matricula_id' => [
                'required', 'integer',
                Rule::exists('matriculas', 'id')->where('empresa_id', $empresaId),
            ],
            'notas.*.nota' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'notas.*.observacao' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
