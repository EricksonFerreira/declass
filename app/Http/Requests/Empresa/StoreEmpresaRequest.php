<?php

namespace App\Http\Requests\Empresa;

use App\Support\DomainNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class StoreEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cnpj' => $this->onlyDigits($this->input('cnpj')),
            'telefone' => $this->onlyDigits($this->input('telefone')),
            'dominio' => DomainNormalizer::normalize($this->input('dominio')),
            'moeda_pagamento' => strtoupper((string) $this->input('moeda_pagamento', 'BRL')),
            'cor_primaria' => $this->normalizeColor($this->input('cor_primaria')),
            'cor_secundaria' => $this->normalizeColor($this->input('cor_secundaria')),
            'cor_terciaria' => $this->normalizeColor($this->input('cor_terciaria')),
            'nfse_municipio_codigo' => $this->onlyDigits($this->input('nfse_municipio_codigo')),
        ]);
    }

    public function rules(): array
    {
        $slugRules = ['required', 'string', 'max:255'];
        $dominioRules = ['nullable', 'string', 'max:255'];

        if (Schema::hasTable('empresas')) {
            $slugRules[] = Rule::unique('empresas', 'slug');
            $dominioRules[] = Rule::unique('empresas', 'dominio');
        }

        return [
            'nome' => ['required', 'string', 'max:255'],
            'slug' => $slugRules,
            'ativo' => ['nullable', 'in:S,N'],
            'cnpj' => ['nullable', 'digits:14'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'telefone' => ['nullable', 'digits_between:10,11'],
            'dominio' => $dominioRules,
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'cor_primaria' => ['nullable', 'regex:/^#[A-F0-9]{6}$/'],
            'cor_secundaria' => ['nullable', 'regex:/^#[A-F0-9]{6}$/'],
            'cor_terciaria' => ['nullable', 'regex:/^#[A-F0-9]{6}$/'],
            'gateway_pagamento' => ['nullable', Rule::in(['stripe', 'mercado_livre', 'paypal'])],
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
            'reserva_atraso_max_minutos' => ['nullable', 'integer', 'min:0', 'max:10080'],
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

        if (!str_starts_with($color, '#')) {
            $color = '#' . $color;
        }

        return $color;
    }
}
