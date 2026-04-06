<?php

namespace App\Http\Requests\OrdemServico;

use Illuminate\Foundation\Http\FormRequest;

class ConcluirOrdemServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'relatorio_geral' => 'required|string|min:3|max:20000',
            'resultados' => 'nullable|array',
            'resultados.*' => 'nullable|string|max:20000',
        ];
    }
}
