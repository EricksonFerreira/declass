<?php

namespace App\Http\Requests\Ia;

use App\Models\EmpresaIaConfig;
use App\Models\IaConfigGlobal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalvarIaGlobalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provedor' => ['required', Rule::in(array_keys(EmpresaIaConfig::PROVEDORES))],
            'api_key' => 'nullable|string|max:200',
            'modelo' => 'nullable|string|max:60',
            'assistente_ativo' => 'nullable|boolean',
            'transcricao_provedor' => ['nullable', Rule::in(array_keys(IaConfigGlobal::TRANSCRICAO_PROVEDORES))],
            'transcricao_key' => 'nullable|string|max:200',
            'transcricao_modelo' => 'nullable|string|max:60',
            'remover_api_key' => 'nullable|boolean',
            'remover_transcricao_key' => 'nullable|boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'provedor' => 'provedor',
            'api_key' => 'API Key',
            'transcricao_provedor' => 'provedor de transcrição',
            'transcricao_key' => 'API Key da transcrição',
        ];
    }
}
