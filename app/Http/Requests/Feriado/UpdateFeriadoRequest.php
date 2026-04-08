<?php

namespace App\Http\Requests\Feriado;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeriadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => 'required|date',
            'nome' => 'required|string|max:255',
            'estacao_id' => 'required|integer|exists:estacoes,id',
            'ativo' => 'required|string|in:S,N',
        ];
    }

    public function messages()
    {
        return [
            'data.required' => 'A data do feriado é obrigatória',
            'nome.required' => 'O nome do feriado é obrigatório',
            'estacao_id.required' => 'Selecione a estação para este feriado',
        ];
    }
}
