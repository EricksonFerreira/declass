<?php

namespace App\Http\Requests\Diario;

use App\Models\Frequencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Chamada em lote: array de frequencias por matricula do diario.
 */
class SalvarFrequenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'frequencias' => ['required', 'array', 'min:1'],
            'frequencias.*.matricula_id' => [
                'required', 'integer',
                Rule::exists('matriculas', 'id')->where('empresa_id', $empresaId),
            ],
            'frequencias.*.status' => ['required', Rule::in(array_keys(Frequencia::STATUS))],
            'frequencias.*.justificativa' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
