<?php

namespace App\Http\Requests;

use App\Models\SaudeAluno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSaudeAlunoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_sanguineo' => ['nullable', 'string', Rule::in(array_keys(SaudeAluno::TIPOS_SANGUINEOS))],
            'alergias' => ['nullable', 'string', 'max:4000'],
            'medicamentos' => ['nullable', 'string', 'max:4000'],
            'condicoes_cronicas' => ['nullable', 'string', 'max:4000'],
            'contato_emergencia_nome' => ['nullable', 'string', 'max:190'],
            'contato_emergencia_parentesco' => ['nullable', 'string', 'max:60'],
            'contato_emergencia_telefone' => ['nullable', 'string', 'max:30'],
            'observacoes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
