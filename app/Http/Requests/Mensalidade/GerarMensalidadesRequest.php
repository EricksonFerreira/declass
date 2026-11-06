<?php

namespace App\Http\Requests\Mensalidade;

use Illuminate\Foundation\Http\FormRequest;

class GerarMensalidadesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'competencia' => ['required', 'date_format:Y-m'],
            'vencimento' => ['required', 'date'],
        ];
    }
}
