<?php

namespace App\Http\Requests\Chamado;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChamadoStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:aguardando_analise,em_atendimento,cancelado,concluido'],
            'justificativa' => ['nullable', 'string', 'max:1000'],
            'solucao' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
