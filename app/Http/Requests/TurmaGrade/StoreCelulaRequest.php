<?php

namespace App\Http\Requests\TurmaGrade;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCelulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dia_semana' => ['required', 'integer', Rule::in(array_keys(\App\Models\HorarioSlot::DIAS))],
            'ordem' => ['required', 'integer', 'min:1', 'max:20'],
            'disciplina_id' => ['required', 'integer', 'exists:disciplinas,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'sala' => ['nullable', 'string', 'max:40'],
        ];
    }
}
