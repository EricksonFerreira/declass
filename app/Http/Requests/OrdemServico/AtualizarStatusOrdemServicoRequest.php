<?php

namespace App\Http\Requests\OrdemServico;

use App\Models\OrdemServico;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtualizarStatusOrdemServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in([
                OrdemServico::STATUS_EM_ANDAMENTO,
                OrdemServico::STATUS_CANCELADA,
            ])],
        ];
    }
}
