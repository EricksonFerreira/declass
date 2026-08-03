<?php

namespace App\Http\Requests\Veiculo;

use Illuminate\Foundation\Http\FormRequest;

class AdicionarDisponibilidadeVeiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $dataHora = trim((string) $this->input('data_hora_disponibilidade', ''));
        if ($dataHora !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataHora)) {
            $this->merge([
                'data_hora_disponibilidade' => $dataHora . ' 00:00',
            ]);
        } elseif ($dataHora !== '' && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $dataHora)) {
            $this->merge([
                'data_hora_disponibilidade' => str_replace('T', ' ', $dataHora) . ':00',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'local_disponibilidade' => ['required', 'integer', 'exists:estacoes,id'],
            'data_hora_disponibilidade' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'local_disponibilidade.required' => 'Selecione a estação de origem.',
            'local_disponibilidade.integer' => 'A estação de origem deve ser um número válido.',
            'local_disponibilidade.exists' => 'A estação de origem informada não foi encontrada.',
            'data_hora_disponibilidade.required' => 'Informe a data e hora de disponibilidade.',
            'data_hora_disponibilidade.date' => 'A data e hora de disponibilidade deve ser uma data válida.',
        ];
    }
}
