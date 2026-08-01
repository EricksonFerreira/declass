<?php

namespace App\Http\Requests\Chamado;

use Illuminate\Foundation\Http\FormRequest;

class StoreChamadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chamado_categoria_id' => ['required', 'integer', 'exists:chamado_categorias,id'],
            'chamado_subcategoria_id' => ['nullable', 'integer', 'exists:chamado_subcategorias,id'],
            'descricao' => ['required', 'string'],
            'anexos.*' => ['nullable', 'file', 'max:10240'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'descricao' => is_string($this->descricao) ? trim($this->descricao) : $this->descricao,
        ]);
    }
}
