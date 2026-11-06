<?php

namespace App\Http\Requests\PlanoMensalidade;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanoMensalidadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'nome' => ['required', 'string', 'max:120'],
            'valor' => ['required', 'numeric', 'min:0', 'max:999999'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
