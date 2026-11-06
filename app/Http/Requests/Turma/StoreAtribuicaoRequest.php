<?php

namespace App\Http\Requests\Turma;

use App\Models\Atribuicao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAtribuicaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'user_id' => [
                'required', 'integer',
                Rule::exists('users', 'id')->where('empresa_id', $empresaId),
            ],
            'disciplina_id' => [
                'nullable', 'integer',
                Rule::exists('disciplinas', 'id')->where('empresa_id', $empresaId),
            ],
            'papel' => ['required', Rule::in(array_keys(Atribuicao::PAPEIS))],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
