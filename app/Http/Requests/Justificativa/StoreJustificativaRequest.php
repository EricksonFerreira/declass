<?php

namespace App\Http\Requests\Justificativa;

use App\Models\Justificativa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJustificativaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'matricula_id' => [
                'required',
                Rule::exists('matriculas', 'id')->where('empresa_id', $empresaId),
            ],
            'disciplina_id' => [
                'nullable',
                Rule::exists('disciplinas', 'id')->where('empresa_id', $empresaId),
            ],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'tipo' => ['required', Rule::in(array_keys(Justificativa::TIPOS))],
            'efeito' => ['required', Rule::in(array_keys(Justificativa::EFEITOS))],
            'motivo' => ['nullable', 'string', 'max:1000'],
            'arquivo' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    public function attributes(): array
    {
        return [
            'matricula_id' => 'aluno',
            'disciplina_id' => 'disciplina',
            'data_inicio' => 'início do afastamento',
            'data_fim' => 'fim do afastamento',
            'efeito' => 'efeito nas faltas',
            'arquivo' => 'documento',
        ];
    }
}
