<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;

class AdicionarDanoCheckoutRevisaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoria_id' => ['required', 'integer', 'exists:categoria_danos,id'],
            'dano_id' => ['required', 'integer', 'exists:danos,id'],
            'local_dano' => ['required', 'string', 'in:direita,esquerda,frente,atras,cima'],
            'localizacao_problema' => ['required', 'string', 'max:255'],
            'tipo_dano' => ['required', 'string', 'max:100'],
            'custo_estimado' => ['nullable', 'numeric', 'min:0'],
            'quilometragem_veiculo' => ['nullable', 'numeric', 'min:0'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
            'fotos' => ['nullable', 'array'],
            'fotos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:8192'],
        ];
    }
}
