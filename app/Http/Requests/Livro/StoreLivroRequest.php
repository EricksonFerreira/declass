<?php

namespace App\Http\Requests\Livro;

use App\Models\Livro;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLivroRequest extends FormRequest
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
            'titulo' => ['required', 'string', 'max:180'],
            'autor' => ['nullable', 'string', 'max:140'],
            'editora' => ['nullable', 'string', 'max:140'],
            'isbn' => ['nullable', 'string', 'max:20'],
            'categoria' => ['nullable', Rule::in(array_keys(Livro::CATEGORIAS))],
            'ano_publicacao' => ['nullable', 'integer', 'min:1400', 'max:2100'],
            'exemplares' => ['required', 'integer', 'min:1', 'max:500'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'localizacao' => ['nullable', 'string', 'max:60'],
            'capa' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
