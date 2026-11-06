<?php

namespace App\Http\Requests\Cardapio;

use App\Models\Cardapio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCardapioRequest extends FormRequest
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
            'data' => ['required', 'date'],
            'tipo' => ['required', 'string', Rule::in(array_keys(Cardapio::TIPOS))],
            'descricao' => ['required', 'string', 'max:4000'],
            'restricoes_alimentares' => ['nullable', 'string', 'max:500'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
