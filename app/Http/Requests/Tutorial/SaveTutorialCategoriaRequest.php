<?php

namespace App\Http\Requests\Tutorial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTutorialCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoriaId = $this->route('categoria')?->id;

        return [
            'nome' => [
                'required', 'string', 'max:255',
                Rule::unique('tutorial_categorias', 'nome')->ignore($categoriaId),
            ],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório',
            'nome.unique' => 'Já existe uma categoria com este nome',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->boolean('ativo', true),
            'ordem' => (int) $this->input('ordem', 0),
        ]);
    }
}
