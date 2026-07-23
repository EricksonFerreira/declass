<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;

class AssinarContratoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
                // Fallback
            }
        }

        if (!$ehMotorista) {
            $rules['veiculo_id'] = 'required|integer|exists:veiculos,id';
            $rules['contrato_id'] = 'required|integer|exists:contrato_checkin,id';
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
        ];
    }
}
