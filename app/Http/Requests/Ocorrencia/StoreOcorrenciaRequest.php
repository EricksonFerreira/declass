<?php

namespace App\Http\Requests\Ocorrencia;

use App\Models\Ocorrencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOcorrenciaRequest extends FormRequest
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
            'escola_id' => [
                'required', 'integer',
                Rule::exists('escolas', 'id')->where('empresa_id', $empresaId),
            ],
            'data' => ['required', 'date'],
            'tipo' => ['required', 'string', Rule::in(array_keys(Ocorrencia::TIPOS))],
            'gravidade' => ['nullable', 'string', Rule::in(array_keys(Ocorrencia::GRAVIDADES))],
            'local' => ['nullable', 'string', 'max:100'],
            'descricao' => ['required', 'string', 'max:4000'],
            'medida_adotada' => ['nullable', 'string', 'max:2000'],
            'notifica_responsavel' => ['nullable', 'boolean'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
