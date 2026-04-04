<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class Empresa extends Model
{
    use HasFactory;

    public const DEFAULT_TEMPLATE_SLUG = 'empresa-padrao';

    protected $table = 'empresas';

    protected $fillable = [
        'nome',
        'slug',
        'ativo',
        'cnpj',
        'email',
        'telefone',
        'dominio',
        'site_apresentacao_ativo',
        'site_apresentacao_modelo',
        'site_apresentacao_titulo',
        'site_apresentacao_subtitulo',
        'site_apresentacao_descricao',
        'site_apresentacao_botao_texto',
        'site_apresentacao_botao_url',
        'site_apresentacao_textos',
        'site_apresentacao_imagens',
        'site_apresentacao_cores',
        'site_apresentacao_imagens_config',
        'logo_path',
        'cor_primaria',
        'cor_secundaria',
        'cor_terciaria',
        'gateway_pagamento',
        'moeda_pagamento',
        'stripe_public_key',
        'stripe_secret',
        'mercado_livre_public_key',
        'mercado_livre_access_token',
        'paypal_client_id',
        'paypal_client_secret',
        'paypal_mode',
        'reserva_escolher_empresa',
        'reserva_selecionar_modelo',
        'empresa_id',
    ];

    protected $casts = [
        'site_apresentacao_textos' => 'array',
        'site_apresentacao_imagens' => 'array',
        'site_apresentacao_cores' => 'array',
        'site_apresentacao_imagens_config' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        $path = is_string($this->logo_path) ? trim($this->logo_path) : '';

        if ($path === '') {
            return null;
        }

        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::url($path);
    }

    protected function stripeSecret(): Attribute
    {
        return $this->encryptedStringAttribute('stripe_secret');
    }

    protected function mercadoLivreAccessToken(): Attribute
    {
        return $this->encryptedStringAttribute('mercado_livre_access_token');
    }

    protected function paypalClientSecret(): Attribute
    {
        return $this->encryptedStringAttribute('paypal_client_secret');
    }

    private function encryptedStringAttribute(string $column): Attribute
    {
        return Attribute::make(
            get: function ($value) use ($column) {
                $raw = $this->getRawOriginal($column);
                if ($raw === null || $raw === '') {
                    return null;
                }

                try {
                    return Crypt::decryptString($raw);
                } catch (DecryptException $e) {
                    // Compatibilidade com dados antigos sem criptografia.
                    return $raw;
                }
            },
            set: function ($value) {
                if ($value === null || $value === '') {
                    return null;
                }

                return Crypt::encryptString((string) $value);
            }
        );
    }
}
