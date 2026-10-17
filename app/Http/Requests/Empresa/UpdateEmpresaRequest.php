<?php

namespace App\Http\Requests\Empresa;

use App\Support\SchemaCache;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpresaRequest extends FormRequest
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
        $empresaId = $this->route('empresa');
        $slugRules = ['required', 'string', 'max:255'];

        if (SchemaCache::hasTable('empresas')) {
            $slugRules[] = Rule::unique('empresas', 'slug')->ignore($empresaId);
        }

        return [
            'nome' => ['required', 'string', 'max:255'],
            'slug' => $slugRules,
            'ativo' => ['nullable', 'in:S,N'],
            'licitacoes_ativo' => ['nullable', 'in:S,N'],
            'ia_limite_tokens' => ['nullable', 'integer', 'min:0'],
            'tags_por_projeto' => ['nullable', 'boolean'],
            'reserva_selecionar_modelo' => ['nullable', 'in:S,N'],
            'site_apresentacao_ativo' => ['nullable', 'in:S,N'],
            'aluguel_app_ativo' => ['nullable', 'in:S,N'],
            'site_tipo_reserva' => ['nullable', Rule::in(['motorista_app', 'reserva_comum', 'ambas'])],
            'adicionar_novas_marcas_automaticamente' => ['nullable', 'in:S,N'],
            'adicionar_novos_modelos_automaticamente' => ['nullable', 'in:S,N'],
            'aluguel_app_exigir_aprovacao_documentos' => ['nullable', 'in:S,N'],
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
            'cnpj' => ['nullable', 'digits:14'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'telefone' => ['nullable', 'digits_between:10,11'],
            'representante' => ['nullable', 'string', 'max:255'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'dominio' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:8192'],
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
            'reserva_atraso_max_minutos' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'reserva_bloqueio_pos_reserva_horas' => ['nullable', 'integer', 'min:0', 'max:720'],
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
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => 'O nome deve ter no máximo 255 caracteres.',
            'slug.required' => 'O campo slug é obrigatório.',
            'slug.string' => 'O slug deve ser uma string.',
            'slug.max' => 'O slug deve ter no máximo 255 caracteres.',
            'slug.unique' => 'Este slug já está em uso.',
            'ativo.in' => 'O campo ativo deve ser S ou N.',
            'cnpj.digits' => 'O CNPJ deve conter 14 dígitos.',
            'email.email' => 'O e-mail deve ser um endereço de e-mail válido.',
            'email.max' => 'O e-mail deve ter no máximo 255 caracteres.',
            'telefone.digits_between' => 'O telefone deve ter entre 10 e 11 dígitos.',
            'dominio.max' => 'O domínio deve ter no máximo 255 caracteres.',
            'logo.image' => 'O arquivo da logo deve ser uma imagem.',
            'logo.mimes' => 'A logo deve ser um arquivo do tipo: jpg, jpeg, png, webp ou svg.',
            'logo.max' => 'A logo não pode ser maior que 8MB.',
            'cor_primaria.regex' => 'A cor primária deve estar no formato hexadecimal (ex: #A1B2C3).',
            'cor_secundaria.regex' => 'A cor secundária deve estar no formato hexadecimal (ex: #A1B2C3).',
            'cor_terciaria.regex' => 'A cor terciária deve estar no formato hexadecimal (ex: #A1B2C3).',
            'gateway_pagamento.in' => 'Gateway de pagamento inválido.',
            'moeda_pagamento.in' => 'Moeda de pagamento inválida.',
            'stripe_public_key.max' => 'A chave pública do Stripe deve ter no máximo 255 caracteres.',
            'mercado_livre_public_key.max' => 'A chave pública do Mercado Livre deve ter no máximo 255 caracteres.',
            'paypal_client_id.max' => 'O Client ID do PayPal deve ter no máximo 255 caracteres.',
            'paypal_mode.in' => 'O modo do PayPal deve ser sandbox ou live.',
            'nfse_ativo.in' => 'O campo NFS-e ativo deve ser S ou N.',
            'nfse_ambiente.in' => 'O ambiente NFS-e deve ser homologacao ou producao.',
            'nfse_municipio_codigo.digits_between' => 'O codigo do municipio deve ter 6 ou 7 digitos.',
            'reserva_atraso_max_minutos.integer' => 'O tempo maximo de atraso deve ser um numero inteiro.',
            'reserva_atraso_max_minutos.min' => 'O tempo maximo de atraso nao pode ser negativo.',
            'reserva_atraso_max_minutos.max' => 'O tempo maximo de atraso nao pode ultrapassar 10080 minutos.',
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
