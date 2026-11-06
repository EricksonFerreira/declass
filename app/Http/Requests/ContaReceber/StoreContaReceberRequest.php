<?php

namespace App\Http\Requests\ContaReceber;

use App\Models\ContaReceber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContaReceberRequest extends FormRequest
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
            'aluno_id' => [
                'nullable', 'integer',
                Rule::exists('alunos', 'id')->where('empresa_id', $empresaId),
            ],
            'descricao' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', Rule::in(array_keys(ContaReceber::CATEGORIAS))],
            'valor' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'data_vencimento' => ['required', 'date'],
            'forma_recebimento' => ['nullable', 'string', Rule::in(array_keys(ContaReceber::FORMAS_RECEBIMENTO))],
            'observacoes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
