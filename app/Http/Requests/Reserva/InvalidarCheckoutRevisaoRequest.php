<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;

class InvalidarCheckoutRevisaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'justificativa' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
