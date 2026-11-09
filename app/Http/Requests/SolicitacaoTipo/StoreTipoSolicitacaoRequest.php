<?php

namespace App\Http\Requests\SolicitacaoTipo;

use App\Models\SolicitacaoTipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTipoSolicitacaoRequest extends FormRequest
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
            'nome' => ['required', 'string', 'max:120'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'exige_anexo' => ['nullable', Rule::in(['S', 'N'])],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
            'campos' => ['nullable', 'array', 'max:30'],
            'campos.*.rotulo' => ['required', 'string', 'max:120'],
            'campos.*.tipo' => ['required', Rule::in(array_keys(SolicitacaoTipo::CAMPO_TIPOS))],
            'campos.*.obrigatoria' => ['nullable', Rule::in(['S', 'N'])],
            'campos.*.opcoes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'campos.*.rotulo.required' => 'Todo campo adicionado precisa de um rótulo.',
            'campos.*.tipo.in' => 'Tipo de campo inválido.',
        ];
    }
}
