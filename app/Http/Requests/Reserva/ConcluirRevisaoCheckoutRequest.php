<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;

class ConcluirRevisaoCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sem_pendencias' => ['nullable', 'boolean'],
            'observacao' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
