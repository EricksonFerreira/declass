<?php

namespace App\Http\Requests\Material;

use App\Models\Material;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaterialRequest extends FormRequest
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
                'nullable', 'integer',
                Rule::exists('escolas', 'id')->where('empresa_id', $empresaId),
            ],
            'nome' => ['required', 'string', 'max:190'],
            'categoria' => ['required', 'string', Rule::in(array_keys(Material::CATEGORIAS))],
            'unidade_medida' => ['required', 'string', Rule::in(array_keys(Material::UNIDADES))],
            'estoque_atual' => ['nullable', 'numeric', 'min:0'],
            'estoque_minimo' => ['nullable', 'numeric', 'min:0'],
            'localizacao' => ['nullable', 'string', 'max:120'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
