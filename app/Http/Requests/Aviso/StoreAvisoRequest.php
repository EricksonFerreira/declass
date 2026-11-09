<?php

namespace App\Http\Requests\Aviso;

use App\Models\Aviso;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAvisoRequest extends FormRequest
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
            'turma_id' => [
                'nullable', 'integer',
                Rule::exists('turmas', 'id')->where('empresa_id', $empresaId),
            ],
            'turma_ids' => ['nullable', 'array'],
            'turma_ids.*' => [
                'integer',
                Rule::exists('turmas', 'id')->where('empresa_id', $empresaId),
            ],
            'titulo' => ['required', 'string', 'max:160'],
            'mensagem' => ['required', 'string', 'max:5000'],
            'tipo' => ['nullable', Rule::in(array_keys(Aviso::TIPOS))],
            'destino' => ['nullable', Rule::in(array_keys(Aviso::DESTINOS))],
            'data_publicacao' => ['required', 'date'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
