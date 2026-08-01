<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;

class AssinarContratoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'modo' => 'nullable|string|in:digital,upload',
        ];

        $codigo = $this->route('codigo') ?: $this->route('id');
        $ehMotorista = false;

        if ($codigo) {
            try {
                $reservaResolvida = app(\App\Services\Reserva\ReservaResolver::class)->resolver((string) $codigo);
                if ($reservaResolvida->ehMotoristaApp()) {
                    $ehMotorista = true;
                }
            } catch (\Throwable $e) {
                // Fallback silencioso
            }
        }

        if (! $ehMotorista) {
            $rules['veiculo_id'] = 'required|integer|exists:veiculos,id';
            $rules['contrato_id'] = 'required|integer|exists:contrato_checkin,id';

            try {
                if ($codigo) {
                    $reservaResolvida = app(\App\Services\Reserva\ReservaResolver::class)->resolver((string) $codigo);
                    if ($reservaResolvida->ehComum()) {
                        $reserva = $reservaResolvida->registro;
                        $usuarioDaReserva = (int) ($reserva->user_id ?? $reserva->usuario_id ?? 0);
                        if (auth()->check() && (int) auth()->id() === $usuarioDaReserva) {
                            $rules['aceite_termo_checkin'] = 'accepted';
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Fallback silencioso
            }
        }

        if ($this->input('modo') === 'upload') {
            $rules['contrato_pdf'] = 'required|file|mimes:pdf|max:10240';
            $rules['assinatura'] = 'nullable';
        } else {
            $rules['assinatura'] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'veiculo_id.required' => 'Você não selecionou uma veiculo.',
            'veiculo_id.integer' => 'A veiculo deve ser um número inteiro.',
            'veiculo_id.exists' => 'A veiculo não existe.',
            'contrato_id.required' => 'Você não selecionou um contrato.',
            'contrato_id.integer' => 'O contrato deve ser um número inteiro.',
            'contrato_id.exists' => 'O contrato não existe.',
            'assinatura.required' => 'A assinatura é obrigatória',
            'aceite_termo_checkin.accepted' => 'Você precisa aceitar o termo de check-in para assinar o contrato.',
        ];
    }
}
