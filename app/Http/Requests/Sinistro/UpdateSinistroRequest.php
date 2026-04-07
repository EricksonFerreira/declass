<?php

namespace App\Http\Requests\Sinistro;

use App\Models\Sinistro;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSinistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'veiculo_id'                     => 'nullable|integer|exists:veiculos,id',
            'reserva_id'                     => 'nullable|integer|exists:reservas,id',
            'cliente_id'                     => 'nullable|integer|exists:users,id',
            'condutor_id'                    => 'nullable|integer|exists:users,id',
            'tipo_sinistro'                  => 'required|string|in:' . implode(',', array_keys(Sinistro::TIPOS)),
            'data_hora_ocorrido'             => 'required|date',
            'local_ocorrido'                 => 'nullable|string|max:255',
            'descricao'                      => 'nullable|string',
            'placa_momento'                  => 'nullable|string|max:20',
            'numero_bo'                      => 'nullable|string|max:100',
            'orgao_bo'                       => 'nullable|string|max:100',
            'terceiros_envolvidos'           => 'nullable|boolean',
            'terceiro_nome'                  => 'nullable|string|max:150',
            'terceiro_telefone'              => 'nullable|string|max:30',
            'terceiro_placa'                 => 'nullable|string|max:20',
            'vitimas'                        => 'nullable|boolean',
            'seguradora'                     => 'nullable|string|max:150',
            'numero_apolice'                 => 'nullable|string|max:100',
            'numero_sinistro_seguradora'     => 'nullable|string|max:100',
            'franquia'                       => 'nullable|numeric|min:0',
            'valor_prejuizo_estimado'        => 'nullable|numeric|min:0',
            'valor_aprovado_seguradora'      => 'nullable|numeric|min:0',
            'valor_responsabilidade_cliente' => 'nullable|numeric|min:0',
            'status'                         => 'required|string|in:' . implode(',', array_keys(Sinistro::STATUS)),
            'observacoes'                    => 'nullable|string',
            'anexo_bo'                       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'anexo_foto1'                    => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
            'anexo_foto2'                    => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
            'anexo_foto3'                    => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
            'anexo_laudo'                    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_sinistro.required'      => 'O tipo de sinistro é obrigatório.',
            'data_hora_ocorrido.required' => 'A data e hora do ocorrido são obrigatórias.',
            'status.required'             => 'O status é obrigatório.',
        ];
    }
}
