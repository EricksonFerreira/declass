<?php

namespace App\Http\Requests\Veiculo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVeiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'escola_id' => [
                'required', 'integer',
                Rule::exists('escolas', 'id')->where('empresa_id', $empresaId),
            ],
            'placa' => ['required', 'string', 'max:12'],
            'modelo' => ['required', 'string', 'max:60'],
            'ano' => ['nullable', 'integer', 'min:1980', 'max:2100'],
            'capacidade' => ['nullable', 'integer', 'min:1', 'max:100'],
            'motorista_nome' => ['nullable', 'string', 'max:100'],
            'motorista_telefone' => ['nullable', 'string', 'max:20'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
