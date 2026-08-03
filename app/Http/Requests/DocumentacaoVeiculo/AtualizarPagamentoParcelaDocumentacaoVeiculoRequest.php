<?php

namespace App\Http\Requests\DocumentacaoVeiculo;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AtualizarPagamentoParcelaDocumentacaoVeiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $dataPagamento = trim((string) $this->input('data_pagamento', ''));

        if ($dataPagamento === '') {
            $this->merge([
                'data_pagamento' => now()->toDateString(),
            ]);
            return;
        }

        if (str_contains($dataPagamento, 'T')) {
            $this->merge([
                'data_pagamento' => Carbon::parse(str_replace('T', ' ', $dataPagamento))->toDateString(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'data_pagamento' => ['required', 'date'],
        ];
    }
}
