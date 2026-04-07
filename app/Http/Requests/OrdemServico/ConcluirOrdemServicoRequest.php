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
            'anexos' => 'nullable|array',
            'anexos.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,txt',
        ];
    }
}
