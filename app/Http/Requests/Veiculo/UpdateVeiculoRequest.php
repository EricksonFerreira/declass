<?php

namespace App\Http\Requests\Veiculo;

use App\Support\EmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateVeiculoRequest extends FormRequest
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
        $empresaId = app(EmpresaContext::class)->empresaId() ?? Auth::user()?->empresa_id;

        $marcaExists = Rule::exists('marcas', 'id');
        $modeloExists = Rule::exists('modelos', 'id');

        if ($empresaId !== null) {
            $marcaExists = $marcaExists->where(fn ($query) => $query->where('empresa_id', (int) $empresaId));
            $modeloExists = $modeloExists->where(fn ($query) => $query->where('empresa_id', (int) $empresaId));
        }

        return [
            'status_veiculo_id' => 'required|string|max:2',
            'marca_id' => ['required', 'integer', $marcaExists],
            'modelo_id' => ['required', 'integer', $modeloExists],
            'ano_fabricacao' => 'required|integer|min:1900|max:' . date('Y'),
            'placa_licenca' => 'required|string|max:10',
            'chassi' => 'required|string|max:17',
            // 'renavam' => 'required|string|max:11',
            'cor' => 'required|string|max:50',
            'numero_motor' => 'required|string|max:50',
            'data_placa_licenca' => 'required|date|before_or_equal:data_validade',
            'data_compra' => 'required|date|before_or_equal:data_placa_licenca',
            'valor_compra' => 'nullable|numeric|min:0',
            'valor_fipe' => 'nullable|numeric|min:0',
            'codigo_fipe' => 'nullable|string|max:20',
            'referencia_fipe' => 'nullable|string|max:20',
            'data_consulta_fipe' => 'nullable|date',
            'taxa_depreciacao_anual' => 'nullable|numeric|min:0|max:100',
            'data_validade' => 'required|date',
            'data_proxima_manutencao' => 'required|date|after_or_equal:today',
            'data_proxima_inspecao' => 'required|date|after_or_equal:today',
            'data_vendido' => 'nullable|date',
            'kms' => 'required|integer|min:0',
            'limite_kms' => 'required|integer|min:0|gte:kms',
            'proxima_revisao_kms' => 'required|integer|min:0|gte:kms',
            'proxima_manutencao_kms' => 'required|integer|min:0|gte:kms',
            // 'vendido' => 'required|string|in:N,S',
        ];
    }

    public function messages()
    {
        return [
            'status_veiculo_id.required' => 'O campo status é obrigatório',
            'status_veiculo_id.max' => 'O campo status deve ter no máximo 1 caractere',
            'ano_fabricacao.required' => 'O campo ano de fabricação é obrigatório',
            'ano_fabricacao.integer' => 'O campo ano de fabricação deve ser um número inteiro',
            'ano_fabricacao.min' => 'O campo ano de fabricação deve ser no mínimo 1900',
            'ano_fabricacao.max' => 'O campo ano de fabricação deve ser no máximo o ano atual',
            'placa_licenca.required' => 'O campo placa é obrigatório',
            'placa_licenca.max' => 'O campo placa deve ter no máximo 10 caracteres',
            'chassi.required' => 'O campo chassi é obrigatório',
            'chassi.max' => 'O campo chassi deve ter no máximo 17 caracteres',
            'renavam.required' => 'O campo RENAVAM é obrigatório',
            'renavam.max' => 'O campo RENAVAM deve ter no máximo 11 caracteres',
            'cor.required' => 'O campo cor é obrigatório',
            'cor.max' => 'O campo cor deve ter no máximo 50 caracteres',
            'numero_motor.required' => 'O campo número do motor é obrigatório',
            'numero_motor.max' => 'O campo número do motor deve ter no máximo 50 caracteres',
            'data_placa_licenca.required' => 'O campo data de emissão da placa/licença é obrigatório',
            'data_placa_licenca.date' => 'O campo data de emissão da placa/licença deve ser uma data válida',
            'data_placa_licenca.before_or_equal' => 'A data de emissão da placa/licença não pode ser posterior à data de validade da documentação/licença',
            'data_compra.required' => 'O campo data de compra é obrigatório',
            'data_compra.date' => 'O campo data de compra deve ser uma data válida',
            'data_compra.before_or_equal' => 'A data de compra não pode ser posterior à emissão da placa/licença',
            'data_validade.required' => 'O campo data de validade é obrigatório',
            'data_validade.date' => 'O campo data de validade deve ser uma data válida',
            'data_proxima_manutencao.required' => 'O campo data da próxima manutenção é obrigatório',
            'data_proxima_manutencao.date' => 'O campo data da próxima manutenção deve ser uma data válida',
            'data_proxima_manutencao.after_or_equal' => 'O campo data da próxima manutenção deve ser hoje ou uma data futura',
            'data_proxima_inspecao.required' => 'O campo data da próxima inspeção é obrigatório',
            'data_proxima_inspecao.date' => 'O campo data da próxima inspeção deve ser uma data válida',
            'data_proxima_inspecao.after_or_equal' => 'O campo data da próxima inspeção deve ser hoje ou uma data futura',
            'data_vendido.date' => 'O campo data de venda deve ser uma data válida',
            'kms.required' => 'O campo quilometragem atual é obrigatório',
            'kms.integer' => 'O campo quilometragem atual deve ser um número inteiro',
            'kms.min' => 'O campo quilometragem atual deve ser no mínimo 0',
            'limite_kms.required' => 'O campo limite de quilometragem é obrigatório',
            'limite_kms.integer' => 'O campo limite de quilometragem deve ser um número inteiro',
            'limite_kms.min' => 'O campo limite de quilometragem deve ser no mínimo 0',
            'limite_kms.gte' => 'O limite de quilometragem não pode ser menor que a quilometragem atual',
            'proxima_revisao_kms.required' => 'O campo quilometragem da próxima revisão é obrigatório',
            'proxima_revisao_kms.integer' => 'O campo quilometragem da próxima revisão deve ser um número inteiro',
            'proxima_revisao_kms.min' => 'O campo quilometragem da próxima revisão deve ser no mínimo 0',
            'proxima_revisao_kms.gte' => 'A quilometragem da próxima revisão não pode ser menor que a quilometragem atual',
            'proxima_manutencao_kms.required' => 'O campo quilometragem da próxima manutenção é obrigatório',
            'proxima_manutencao_kms.integer' => 'O campo quilometragem da próxima manutenção deve ser um número inteiro',
            'proxima_manutencao_kms.min' => 'O campo quilometragem da próxima manutenção deve ser no mínimo 0',
            'proxima_manutencao_kms.gte' => 'A quilometragem da próxima manutenção não pode ser menor que a quilometragem atual',
            'vendido.required' => 'O campo status de venda é obrigatório',
            'vendido.in' => 'O campo status de venda deve ser "N" (Não Vendido) ou "S" (Vendido)',
        ];
    }
}
