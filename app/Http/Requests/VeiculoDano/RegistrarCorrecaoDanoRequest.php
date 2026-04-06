<?php

namespace App\Http\Requests\VeiculoDano;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarCorrecaoDanoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'correcao_descricao' => 'required|string|max:5000',
        ];
    }
}
