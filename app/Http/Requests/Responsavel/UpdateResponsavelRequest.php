<?php

namespace App\Http\Requests\Responsavel;

use App\Models\Responsavel;
use Illuminate\Validation\Rule;

class UpdateResponsavelRequest extends StoreResponsavelRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $responsavelId = $this->route('responsavel');
        $pessoaId = $responsavelId
            ? Responsavel::query()->whereKey($responsavelId)->value('pessoa_id')
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
