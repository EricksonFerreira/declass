<?php

namespace App\Http\Requests\Empresa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UpdateEmpresaRequest extends FormRequest
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
            'moeda_pagamento' => strtoupper((string) $this->input('moeda_pagamento', 'BRL')),
            'cor_primaria' => $this->normalizeColor($this->input('cor_primaria')),
            'cor_secundaria' => $this->normalizeColor($this->input('cor_secundaria')),
            'cor_terciaria' => $this->normalizeColor($this->input('cor_terciaria')),
        ]);
    }

    public function rules(): array
    {
        $empresaId = $this->route('empresa');
        $slugRules = ['required', 'string', 'max:255'];

        if (Schema::hasTable('empresas')) {
            $slugRules[] = Rule::unique('empresas', 'slug')->ignore($empresaId);
        }

        return [
            'nome' => ['required', 'string', 'max:255'],
            'slug' => $slugRules,
            'ativo' => ['nullable', 'in:S,N'],
            'cnpj' => ['nullable', 'digits:14'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'telefone' => ['nullable', 'digits_between:10,11'],
            'dominio' => ['nullable', 'string', 'max:255'],
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
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome da empresa e obrigatorio.',
            'slug.required' => 'O slug da empresa e obrigatorio.',
            'slug.unique' => 'Este slug ja esta em uso por outra empresa.',
            'cnpj.digits' => 'O CNPJ deve conter 14 digitos.',
            'email.email' => 'Informe um e-mail valido.',
            'telefone.digits_between' => 'O telefone deve conter 10 ou 11 digitos.',
            'logo.image' => 'O logo deve ser uma imagem valida.',
            'logo.mimes' => 'O logo deve estar em um formato valido: jpg, jpeg, png, webp ou svg.',
            'logo.max' => 'O logo deve ter no maximo 2MB.',
            'cor_primaria.regex' => 'A cor primaria deve estar no formato hexadecimal. Ex: #1F2937.',
            'cor_secundaria.regex' => 'A cor secundaria deve estar no formato hexadecimal. Ex: #334155.',
            'cor_terciaria.regex' => 'A cor terciaria deve estar no formato hexadecimal. Ex: #64748B.',
            'gateway_pagamento.in' => 'Gateway de pagamento invalido.',
            'moeda_pagamento.in' => 'A moeda de pagamento deve ser BRL, USD ou EUR.',
            'paypal_mode.in' => 'O modo do PayPal deve ser sandbox ou live.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'slug' => 'slug',
            'ativo' => 'status',
            'cnpj' => 'CNPJ',
            'email' => 'e-mail',
            'telefone' => 'telefone',
            'dominio' => 'dominio',
            'logo' => 'logo',
            'cor_primaria' => 'cor primaria',
            'cor_secundaria' => 'cor secundaria',
            'cor_terciaria' => 'cor terciaria',
            'gateway_pagamento' => 'gateway de pagamento',
            'moeda_pagamento' => 'moeda de pagamento',
            'stripe_public_key' => 'Stripe Public Key',
            'stripe_secret' => 'Stripe Secret',
            'mercado_livre_public_key' => 'Mercado Livre Public Key',
            'mercado_livre_access_token' => 'Mercado Livre Access Token',
            'paypal_client_id' => 'PayPal Client ID',
            'paypal_client_secret' => 'PayPal Client Secret',
            'paypal_mode' => 'modo do PayPal',
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
