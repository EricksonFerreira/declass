<?php

namespace App\Http\Requests\Veiculo;

use Illuminate\Foundation\Http\FormRequest;

class StoreVeiculoRequest extends FormRequest
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
        return [
            'status_veiculo_id' => 'required|string|max:2',
            'tipo_frota_id' => 'required|integer|exists:tipo_frota,id',
            'grupo_frota_id' => 'required|integer|exists:grupos_frota,id',
            'marca_id' => 'required|integer|exists:marcas,id',
            'modelo_id' => 'required|integer|exists:modelos,id',
            'data_vendido' => 'nullable|date',
            'ano_fabricacao' => 'required|integer|min:1900|max:' . date('Y'),
            'ano_modelo' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'placa_licenca' => 'required|string|max:10',
            'chassi' => 'required|string|max:17',
            'cor' => 'required|string|max:50',
            'numero_motor' => 'required|string|max:50',
            'data_placa_licenca' => 'required|date',
            'data_compra' => 'required|date',
            'data_validade' => 'nullable|date',
            'data_proxima_manutencao' => 'required|date',
            'data_proxima_inspecao' => 'required|date',
            'kms' => 'required|integer|min:0',
            'limite_kms' => 'required|integer|min:0',
            'proxima_revisao_kms' => 'required|integer|min:0',
            'proxima_manutencao_kms' => 'required|integer|min:0',
            'local_disponibilidade' => 'required|string|max:11',
            'data_hora_disponibilidade' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'status_veiculo_id.required' => 'O campo status é obrigatório',
            'status_veiculo_id.max' => 'O campo status deve ter no máximo 2 caracteres',
            'tipo_frota_id.required' => 'O campo tipo de frota é obrigatório',
            'tipo_frota_id.integer' => 'O campo tipo de frota deve ser um número inteiro',
            'tipo_frota_id.exists' => 'O campo tipo de frota deve existir na tabela tipo_frotas',
            'grupo_frota_id.required' => 'O campo grupo de frota é obrigatório',
            'grupo_frota_id.integer' => 'O campo grupo de frota deve ser um número inteiro',
            'grupo_frota_id.exists' => 'O campo grupo de frota deve existir na tabela grupo_frotas',
            'marca_id.required' => 'O campo marca é obrigatório',
            'marca_id.integer' => 'O campo marca deve ser um número inteiro',
            'marca_id.exists' => 'O campo marca deve existir na tabela marcas',
            'modelo_id.required' => 'O campo modelo é obrigatório',
            'modelo_id.integer' => 'O campo modelo deve ser um número inteiro',
            'modelo_id.exists' => 'O campo modelo deve existir na tabela modelos',
            'data_vendido.date' => 'O campo data de venda deve ser uma data válida',
            'ano_fabricacao.required' => 'O campo ano de fabricação é obrigatório',
            'ano_fabricacao.integer' => 'O campo ano de fabricação deve ser um número inteiro',
            'ano_fabricacao.min' => 'O campo ano de fabricação deve ser no mínimo 1900',
            'ano_fabricacao.max' => 'O campo ano de fabricação deve ser no máximo o ano atual',
            'ano_modelo.required' => 'O campo ano do modelo é obrigatório',
            'ano_modelo.integer' => 'O campo ano do modelo deve ser um número inteiro',
            'ano_modelo.min' => 'O campo ano do modelo deve ser no mínimo 1900',
            'ano_modelo.max' => 'O campo ano do modelo deve ser no máximo o próximo ano',
            'placa_licenca.required' => 'O campo placa é obrigatório',
            'placa_licenca.max' => 'O campo placa deve ter no máximo 10 caracteres',
            'chassi.required' => 'O campo chassi é obrigatório',
            'chassi.max' => 'O campo chassi deve ter no máximo 17 caracteres',
            'cor.required' => 'O campo cor é obrigatório',
            'cor.max' => 'O campo cor deve ter no máximo 50 caracteres',
            'numero_motor.required' => 'O campo número do motor é obrigatório',
            'numero_motor.max' => 'O campo número do motor deve ter no máximo 50 caracteres',
            'data_placa_licenca.required' => 'O campo data de emissão da placa/licença é obrigatório',
            'data_placa_licenca.date' => 'O campo data de emissão da placa/licença deve ser uma data válida',
            'data_compra.required' => 'O campo data de compra é obrigatório',
            'data_compra.date' => 'O campo data de compra deve ser uma data válida',
            'data_validade.date' => 'O campo data de validade deve ser uma data válida',
            'data_proxima_manutencao.required' => 'O campo
            data da próxima manutenção é obrigatório',
            'data_proxima_manutencao.date' => 'O campo data da próxima manutenção deve ser uma data válida',
            'data_proxima_inspecao.required' => 'O campo data da próxima inspeção é obrigatório',
            'data_proxima_inspecao.date' => 'O campo data da próxima inspeção deve ser uma data válida',
            'kms.required' => 'O campo quilometragem atual é obrigatório',
            'kms.integer' => 'O campo quilometragem atual deve ser um número inteiro',
            'kms.min' => 'O campo quilometragem atual deve ser no mínimo 0',
            'limite_kms.required' => 'O campo limite de quilometragem é obrigatório',
            'limite_kms.integer' => 'O campo limite de quilometragem deve ser um número inteiro',
            'limite_kms.min' => 'O campo limite de quilometragem deve ser no mínimo 0',
            'proxima_revisao_kms.required' => 'O campo quilometragem da próxima revisão é obrigatório',
            'proxima_revisao_kms.integer' => 'O campo quilometragem da próxima revisão deve ser um número inteiro',
            'proxima_revisao_kms.min' => 'O campo quilometragem da próxima revisão deve ser no mínimo 0',
            'proxima_manutencao_kms.required' => 'O campo quilometragem da próxima manutenção é obrigatório',
            'proxima_manutencao_kms.integer' => 'O campo quilometragem da próxima manutenção deve ser um número inteiro',
            'proxima_manutencao_kms.min' => 'O campo quilometragem da próxima manutenção deve ser no mínimo 0',
            'local_disponibilidade.required' => 'O campo local de disponibilidade é obrigatório',
            'local_disponibilidade.max' => 'O campo local de disponibilidade deve ter no máximo 11 caracteres',
            'data_hora_disponibilidade.required' => 'O campo data e hora de disponibilidade é obrigatório',
            'data_hora_disponibilidade.date' => 'O campo data e hora de disponibilidade deve ser uma data válida',
        ];

            // 'status_veiculo_id.required' => 'O campo status é obrigatório',
            // 'status_veiculo_id.max' => 'O campo status deve ter no máximo 1 caractere',
            // 'ano_fabricacao.required' => 'O campo ano de fabricação é obrigatório',
            // 'ano_fabricacao.integer' => 'O campo ano de fabricação deve ser um número inteiro',
            // 'ano_fabricacao.min' => 'O campo ano de fabricação deve ser no mínimo 1900',
            // 'ano_fabricacao.max' => 'O campo ano de fabricação deve ser no máximo o ano atual',
            // 'ano_modelo.required' => 'O campo ano do modelo é obrigatório',
            // 'ano_modelo.integer' => 'O campo ano do modelo deve ser um número inteiro',
            // 'ano_modelo.min' => 'O campo ano do modelo deve ser no mínimo 1900',
            // 'ano_modelo.max' => 'O campo ano do modelo deve ser no máximo o próximo ano',
            // 'placa_licenca.required' => 'O campo placa é obrigatório',
            // 'placa_licenca.max' => 'O campo placa deve ter no máximo 10 caracteres',
            // 'chassi.required' => 'O campo chassi é obrigatório',
            // 'chassi.max' => 'O campo chassi deve ter no máximo 17 caracteres',
            // 'renavam.required' => 'O campo RENAVAM é obrigatório',
            // 'renavam.max' => 'O campo RENAVAM deve ter no máximo 11 caracteres',
            // 'cor.required' => 'O campo cor é obrigatório',
            // 'cor.max' => 'O campo cor deve ter no máximo 50 caracteres',
            // 'numero_motor.required' => 'O campo número do motor é obrigatório',
            // 'numero_motor.max' => 'O campo número do motor deve ter no máximo 50 caracteres',
            // 'data_placa_licenca.required' => 'O campo data de emissão da placa/licença é obrigatório',
            // 'data_placa_licenca.date' => 'O campo data de emissão da placa/licença deve ser uma data válida',
            // 'data_compra.required' => 'O campo data de compra é obrigatório',
            // 'data_compra.date' => 'O campo data de compra deve ser uma data válida',
            // 'data_validade.required' => 'O campo data de validade é obrigatório',
            // 'data_validade.date' => 'O campo data de validade deve ser uma data válida',
            // 'data_proxima_manutencao.required' => 'O campo data da próxima manutenção é obrigatório',
            // 'data_proxima_manutencao.date' => 'O campo data da próxima manutenção deve ser uma data válida',
            // 'data_proxima_inspecao.required' => 'O campo data da próxima inspeção é obrigatório',
            // 'data_proxima_inspecao.date' => 'O campo data da próxima inspeção deve ser uma data válida',
            // 'data_vendido.date' => 'O campo data de venda deve ser uma data válida',
            // 'kms.required' => 'O campo quilometragem atual é obrigatório',
            // 'kms.integer' => 'O campo quilometragem atual deve ser um número inteiro',
            // 'kms.min' => 'O campo quilometragem atual deve ser no mínimo 0',
            // 'limite_kms.required' => 'O campo limite de quilometragem é obrigatório',
            // 'limite_kms.integer' => 'O campo limite de quilometragem deve ser um número inteiro',
            // 'limite_kms.min' => 'O campo limite de quilometragem deve ser no mínimo 0',
            // 'proxima_revisao_kms.required' => 'O campo quilometragem da próxima revisão é obrigatório',
            // 'proxima_revisao_kms.integer' => 'O campo quilometragem da próxima revisão deve ser um número inteiro',
            // 'proxima_revisao_kms.min' => 'O campo quilometragem da próxima revisão deve ser no mínimo 0',
            // 'proxima_manutencao_kms.required' => 'O campo quilometragem da próxima manutenção é obrigatório',
            // 'proxima_manutencao_kms.integer' => 'O campo quilometragem da próxima manutenção deve ser um número inteiro',
            // 'proxima_manutencao_kms.min' => 'O campo quilometragem da próxima manutenção deve ser no mínimo 0',
            // 'vendido.required' => 'O campo status de venda é obrigatório',
            // 'vendido.in' => 'O campo status de venda deve ser "N" (Não Vendido) ou "S" (Vendido)',
    }
}
