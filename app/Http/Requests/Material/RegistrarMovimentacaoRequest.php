<?php

namespace App\Http\Requests\Material;

use App\Models\MovimentacaoEstoque;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrarMovimentacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', Rule::in(array_keys(MovimentacaoEstoque::TIPOS))],
            'quantidade' => ['required', 'numeric', 'min:0.01'],
            'motivo' => ['nullable', 'string', 'max:1000'],
            'data' => ['nullable', 'date'],
        ];
    }
}
