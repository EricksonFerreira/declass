<?php

namespace App\Http\Requests\Empresa;

use App\Support\DomainNormalizer;
use App\Support\SchemaCache;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $camposPersonalizados = $this->input('aluguel_app_campos_personalizados');
        if (is_string($camposPersonalizados)) {
            $decoded = json_decode($camposPersonalizados, true);
            $camposPersonalizados = is_array($decoded) ? $decoded : [];
        }

        $this->merge([
            'cnpj' => $this->onlyDigits($this->input('cnpj')),
            'telefone' => $this->onlyDigits($this->input('telefone')),
            'dominio' => DomainNormalizer::normalize($this->input('dominio')),
            'moeda_pagamento' => strtoupper((string) $this->input('moeda_pagamento', 'BRL')),
            'cor_primaria' => $this->normalizeColor($this->input('cor_primaria')),
            'cor_secundaria' => $this->normalizeColor($this->input('cor_secundaria')),
            'cor_terciaria' => $this->normalizeColor($this->input('cor_terciaria')),
            'nfse_municipio_codigo' => $this->onlyDigits($this->input('nfse_municipio_codigo')),
            'aluguel_app_campos_personalizados' => $camposPersonalizados,
        ]);
    }

    public function rules(): array
    {
        $slugRules = ['required', 'string', 'max:255'];
        $dominioRules = ['nullable', 'string', 'max:255'];

        if (SchemaCache::hasTable('empresas')) {
            $slugRules[] = Rule::unique('empresas', 'slug');
            $dominioRules[] = Rule::unique('empresas', 'dominio');
        }

        return [
            'nome' => ['required', 'string', 'max:255'],
            'slug' => $slugRules,
            'ativo' => ['nullable', 'in:S,N'],
            'licitacoes_ativo' => ['nullable', 'in:S,N'],
            'ia_limite_tokens' => ['nullable', 'integer', 'min:0'],
            'tags_por_projeto' => ['nullable', 'boolean'],
            'visao_restrita' => ['nullable', 'boolean'],
            'cnpj' => ['nullable', 'digits:14'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'telefone' => ['nullable', 'digits_between:10,11'],
            'representante' => ['nullable', 'string', 'max:255'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'dominio' => $dominioRules,
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'cor_primaria' => ['nullable', 'regex:/^#[A-F0-9]{6}$/'],
            'cor_secundaria' => ['nullable', 'regex:/^#[A-F0-9]{6}$/'],
            'cor_terciaria' => ['nullable', 'regex:/^#[A-F0-9]{6}$/'],
            'gateway_pagamento' => ['nullable', Rule::in(['nenhum', 'stripe', 'mercado_livre', 'paypal'])],
            'moeda_pagamento' => ['nullable', Rule::in(['BRL', 'USD', 'EUR'])],
            'stripe_public_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret' => ['nullable', 'string'],
            'mercado_livre_public_key' => ['nullable', 'string', 'max:255'],
            'mercado_livre_access_token' => ['nullable', 'string'],
            'paypal_client_id' => ['nullable', 'string', 'max:255'],
            'paypal_client_secret' => ['nullable', 'string'],
            'paypal_mode' => ['nullable', Rule::in(['sandbox', 'live'])],
            'reserva_escolher_empresa' => ['nullable', Rule::in(['S', 'N'])],
            'reserva_selecionar_modelo' => ['nullable', Rule::in(['S', 'N'])],
            'site_apresentacao_ativo' => ['nullable', Rule::in(['S', 'N'])],
            'aluguel_app_ativo' => ['nullable', Rule::in(['S', 'N'])],
            'site_tipo_reserva' => ['nullable', Rule::in(['motorista_app', 'reserva_comum', 'ambas'])],
            'reserva_atraso_max_minutos' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'reserva_bloqueio_pos_reserva_horas' => ['nullable', 'integer', 'min:0', 'max:720'],
            'adicionar_novas_marcas_automaticamente' => ['nullable', Rule::in(['S', 'N'])],
            'adicionar_novos_modelos_automaticamente' => ['nullable', Rule::in(['S', 'N'])],
            'calcao_ativo' => ['nullable', 'in:S,N'],
            'calcao_tipo_regra_valor' => ['nullable', Rule::in(['fixo', 'percentual'])],
            'calcao_valor' => ['nullable', 'numeric', 'min:0'],
            'calcao_valor_minimo' => ['nullable', 'numeric', 'min:0'],
            'calcao_valor_maximo' => ['nullable', 'numeric', 'min:0'],
            'calcao_grupos' => ['nullable', 'array'],
            'calcao_grupos.*.ativo' => ['nullable', 'in:S,N'],
            'calcao_grupos.*.tipo_regra_valor' => ['nullable', Rule::in(['fixo', 'percentual'])],
            'calcao_grupos.*.valor' => ['nullable', 'numeric', 'min:0'],
            'calcao_grupos.*.valor_minimo' => ['nullable', 'numeric', 'min:0'],
            'calcao_grupos.*.valor_maximo' => ['nullable', 'numeric', 'min:0'],
            'aluguel_app_exigir_aprovacao_documentos' => ['nullable', Rule::in(['S', 'N'])],
            'aluguel_app_documentos_obrigatorios' => ['nullable', 'array'],
            'aluguel_app_documentos_obrigatorios.*' => ['nullable', Rule::in([
                'cnh_definitiva',
                'cnh_ear',
                'comprovante_residencia',
                'comprovante_cadastro_app',
                'documento_adicional',
                'nenhum',
            ])],
            'aluguel_app_documento_adicional_label' => ['nullable', 'string', 'max:120'],
            'aluguel_app_campos_personalizados' => ['nullable', 'array'],
            'aluguel_app_campos_personalizados.*.key' => ['required_with:aluguel_app_campos_personalizados', 'string', 'max:80'],
            'aluguel_app_campos_personalizados.*.label' => ['required_with:aluguel_app_campos_personalizados', 'string', 'max:120'],
            'aluguel_app_campos_personalizados.*.type' => ['required_with:aluguel_app_campos_personalizados', Rule::in(['arquivo', 'texto', 'texto_longo', 'lista', 'data'])],
            'aluguel_app_campos_personalizados.*.selected' => ['nullable', 'boolean'],
            'aluguel_app_campos_personalizados.*.obrigatorio' => ['nullable', 'boolean'],
            'aluguel_app_campos_personalizados.*.ordem' => ['nullable', 'integer', 'min:1'],
            'aluguel_app_campos_personalizados.*.options' => ['nullable', 'array'],
            'aluguel_app_campos_personalizados.*.options.*' => ['nullable', 'string', 'max:120'],
            'aluguel_app_campos_personalizados.*.multiple' => ['nullable', 'boolean'],
            'aluguel_app_campos_personalizados.*.condicao' => ['nullable', 'array'],
            'aluguel_app_campos_personalizados.*.condicao.campo_key' => ['nullable', 'string', 'max:80'],
            'aluguel_app_campos_personalizados.*.condicao.operador' => ['nullable', Rule::in(['igual', 'diferente', 'preenchido', 'vazio'])],
            'aluguel_app_campos_personalizados.*.condicao.valor' => ['nullable'],
            'aluguel_app_campos_personalizados.*.condicao.valor.*' => ['nullable', 'string', 'max:120'],
            'nfse_ativo' => ['nullable', Rule::in(['S', 'N'])],
            'nfse_ambiente' => ['nullable', Rule::in(['homologacao', 'producao'])],
            'nfse_provedor' => ['nullable', 'string', 'max:50'],
            'nfse_client_id' => ['nullable', 'string', 'max:150'],
            'nfse_client_secret' => ['nullable', 'string'],
            'nfse_inscricao_municipal' => ['nullable', 'string', 'max:30'],
            'nfse_municipio_codigo' => ['nullable', 'digits_between:6,7'],
            'nfse_codigo_servico' => ['nullable', 'string', 'max:20'],
            'nfse_cnae' => ['nullable', 'string', 'max:20'],
            'nfse_descricao_servico' => ['nullable', 'string'],
            'nfse_aliquota' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nfse_tributacao_issqn' => ['nullable', 'integer', 'min:1', 'max:4'],
            'nfse_tomador_numero_padrao' => ['nullable', 'string', 'max:20'],
            'nfse_tomador_bairro_padrao' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function messages(): array
    {
        return [
            'reserva_bloqueio_pos_reserva_horas.integer' => 'O bloqueio pos-reserva deve ser um numero inteiro de horas.',
            'reserva_bloqueio_pos_reserva_horas.min' => 'O bloqueio pos-reserva nao pode ser negativo.',
            'reserva_bloqueio_pos_reserva_horas.max' => 'O bloqueio pos-reserva nao pode ultrapassar 720 horas.',
            'calcao_tipo_regra_valor.in' => 'O tipo de regra do calcao deve ser fixo ou percentual.',
            'calcao_valor.numeric' => 'O valor padrao do calcao deve ser numerico.',
            'calcao_valor_minimo.numeric' => 'O valor minimo do calcao deve ser numerico.',
            'calcao_valor_maximo.numeric' => 'O valor maximo do calcao deve ser numerico.',
        ];
    }

    private function onlyDigits(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return preg_replace('/\D+/', '', $value);
    }

    private function normalizeColor(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $color = strtoupper(trim($value));

        if (! str_starts_with($color, '#')) {
            $color = '#'.$color;
        }

        return $color;
    }
}
