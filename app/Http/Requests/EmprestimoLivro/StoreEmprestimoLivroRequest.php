<?php

namespace App\Http\Requests\EmprestimoLivro;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmprestimoLivroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'aluno_id' => [
                'required', 'integer',
                Rule::exists('alunos', 'id')->where('empresa_id', $empresaId),
            ],
            'livro_id' => [
                'required', 'integer',
                Rule::exists('livros', 'id')->where('empresa_id', $empresaId),
            ],
            'data_emprestimo' => ['required', 'date'],
            'data_devolucao_prevista' => ['required', 'date', 'after_or_equal:data_emprestimo'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
