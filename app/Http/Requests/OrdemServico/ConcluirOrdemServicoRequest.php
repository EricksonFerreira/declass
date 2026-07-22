<?php

namespace App\Http\Requests\OrdemServico;

use Illuminate\Foundation\Http\FormRequest;

class ConcluirOrdemServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'relatorio_geral' => 'required|string|min:3|max:20000',
            'valor_pecas' => 'nullable|numeric|min:0|max:999999999.99',
            'valor_mao_obra' => 'nullable|numeric|min:0|max:999999999.99',
            'resultados' => 'nullable|array',
            'resultados.*' => 'nullable|string|max:20000',
            'anexos' => 'nullable|array',
            'anexos.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,txt',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'valor_pecas' => $this->normalizarMoeda($this->input('valor_pecas')),
            'valor_mao_obra' => $this->normalizarMoeda($this->input('valor_mao_obra')),
        ]);
    }

    private function normalizarMoeda(mixed $valor): ?string
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return null;
        }

        $valor = str_replace(['R$', ' '], '', $valor);
        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return $valor;
    }
}
