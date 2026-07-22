<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;

class FinalizarCheckoutVistoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quilometragem_devolucao' => 'required|numeric|min:0',
            'nivel_combustivel_devolucao' => 'required|string|in:Reserva,1/8,1/4,3/8,1/2,5/8,3/4,7/8,Cheio',
            'data_devolucao' => 'required|date',
            'hora_devolucao' => 'required|date_format:H:i',
            'notas_devolucao' => 'nullable|string|max:2000',
            'observacoes_devolucao' => 'nullable|string|max:2000',
            'foto_direita_co' => 'nullable|image|max:8192',
            'foto_esquerda_co' => 'nullable|image|max:8192',
            'foto_frente_co' => 'nullable|image|max:8192',
            'foto_tras_co' => 'nullable|image|max:8192',
            'foto_velocimetro_co' => 'nullable|image|max:8192',
            'foto_combustivel_co' => 'nullable|image|max:8192',
            'assinatura' => 'required|string',
        ];
    }
}
