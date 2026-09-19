<?php

namespace App\Http\Requests\Ia;

use App\Models\EmpresaIaConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalvarConfiguracaoIaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provedor_padrao' => ['required', Rule::in(array_keys(EmpresaIaConfig::PROVEDORES))],
            'tokens_limite' => 'required|integer|min:256|max:32000',
            'anonimizar' => 'nullable|boolean',
            'tamanho_resumo' => ['required', Rule::in(array_keys(EmpresaIaConfig::TAMANHOS_RESUMO))],
            'instrucoes_extras' => 'nullable|string|max:4000',
            'deepseek_key' => 'nullable|string|max:200',
            'deepseek_model' => 'nullable|string|max:60',
            'openai_key' => 'nullable|string|max:200',
            'openai_model' => 'nullable|string|max:60',
            'remover_deepseek_key' => 'nullable|boolean',
            'remover_openai_key' => 'nullable|boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'provedor_padrao' => 'provedor padrão',
            'tokens_limite' => 'limite de tokens',
            'tamanho_resumo' => 'tamanho do resumo',
            'instrucoes_extras' => 'instruções extras',
            'deepseek_key' => 'API Key DeepSeek',
            'openai_key' => 'API Key OpenAI',
        ];
    }
}
