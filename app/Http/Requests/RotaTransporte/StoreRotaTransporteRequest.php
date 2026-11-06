<?php

namespace App\Http\Requests\RotaTransporte;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRotaTransporteRequest extends FormRequest
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
                'required', 'integer',
                Rule::exists('escolas', 'id')->where('empresa_id', $empresaId),
            ],
            'veiculo_id' => [
                'nullable', 'integer',
                Rule::exists('veiculos', 'id')->where('empresa_id', $empresaId),
            ],
            'nome' => ['required', 'string', 'max:80'],
            'horario_ida' => ['nullable', 'date_format:H:i'],
            'horario_volta' => ['nullable', 'date_format:H:i'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
            'paradas' => ['nullable', 'array', 'max:30'],
            'paradas.*.id' => ['nullable', 'integer'],
            'paradas.*.nome' => ['required', 'string', 'max:80'],
            'paradas.*.ordem' => ['nullable', 'integer', 'min:1', 'max:50'],
            'paradas.*.horario_previsto' => ['nullable', 'date_format:H:i'],
        ];
    }
}
