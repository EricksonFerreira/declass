<?php

namespace App\Http\Requests\Multa;

use App\Models\Multa;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMultaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reserva_id'         => 'nullable|integer|exists:reservas,id',
            'veiculo_id'         => 'nullable|integer|exists:veiculos,id',
            'cliente_id'         => 'nullable|integer|exists:users,id',
            'condutor_id'        => 'nullable|integer|exists:users,id',
            'placa_momento'      => 'nullable|string|max:20',
            'numero_auto'        => 'nullable|string|max:100',
            'orgao_autuador'     => 'nullable|string|max:150',
            'data_hora_infracao' => 'nullable|date',
            'local_infracao'     => 'nullable|string|max:500',
            'descricao'          => 'nullable|string',
            'valor_original'     => 'required|numeric|min:0',
            'valor_desconto'     => 'nullable|numeric|min:0',
            'valor_final'        => 'required|numeric|min:0',
            'pontos'             => 'nullable|integer|min:0|max:10',
            'data_vencimento'    => 'nullable|date',
            'status_multa'       => 'required|string|in:' . implode(',', array_keys(Multa::STATUS_MULTA)),
            'status_desconto'    => 'required|string|in:' . implode(',', array_keys(Multa::STATUS_DESCONTO)),
            'observacao'         => 'nullable|string',
            'anexo_comprovante'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'anexo_pdf'          => 'nullable|file|mimes:pdf|max:10240',
        ];
    }
}
