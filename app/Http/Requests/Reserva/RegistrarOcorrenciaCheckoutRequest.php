<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarOcorrenciaCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $modo = (string) $this->input('modo_cobranca', 'nao_cobrar');
        $remover = $modo === 'remover_cobranca';

        return [
            'titulo' => [$remover ? 'nullable' : 'required', 'string', 'max:255'],
            'descricao' => [$remover ? 'nullable' : 'required', 'string', 'max:3000'],
            'valor' => ['nullable', 'numeric', 'min:0'],
            'justificativa' => ['nullable', 'string', 'max:3000'],
            'dano_id' => ['required', 'exists:veiculo_danos,id'],
            'modo_cobranca' => ['required', 'in:cobrar,nao_cobrar,remover_cobranca'],
            'usar_calcao' => ['nullable', 'boolean'],
            'status_revisao' => ['nullable', 'in:pendente,cobrar_cliente,nao_cobrar,ja_existente,desgaste_normal,possivel_sinistro'],
            'status_reparo' => ['nullable', 'in:pendente,em_os,em_correcao,corrigida,nao_requer_reparo'],
            'nao_requer_reparo_justificativa' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
