<?php

namespace App\Http\Requests\Aluno;

use App\Models\Aluno;
use Illuminate\Validation\Rule;

class UpdateAlunoRequest extends StoreAlunoRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // CPF unico por empresa, ignorando a pessoa do proprio aluno.
        $alunoId = $this->route('aluno');
        $pessoaId = $alunoId
            ? Aluno::query()->whereKey($alunoId)->value('pessoa_id')
            : null;

        $empresaId = $this->user()?->empresa_id;

        $rules['cpf'] = [
            'nullable', 'string', 'max:20',
            Rule::unique('pessoas', 'cpf')
                ->where('empresa_id', $empresaId)
                ->ignore($pessoaId, 'id'),
        ];

        return $rules;
    }
}
