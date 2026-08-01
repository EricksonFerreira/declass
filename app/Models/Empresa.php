<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\EmpresaPlano;
use App\Models\EmpresaPlanoPagamento;
use App\Models\OnboardingStep;
use App\Models\ArquivoRastreado;
use App\Models\Concerns\HasAuditColumns;

class Empresa extends Model
{
    use HasFactory, HasAuditColumns;

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
        'aluguel_app_ativo',
        'site_tipo_reserva',
        'aluguel_app_exigir_aprovacao_documentos',
        'aluguel_app_documentos_obrigatorios',
        'aluguel_app_documento_adicional_label',
        'aluguel_app_campos_personalizados',
        'gateway_pagamento',
        'gateway_customer_id',
        'moeda_pagamento',
        'stripe_public_key',
        'stripe_secret',
        'mercado_livre_public_key',
        'mercado_livre_access_token',
        'paypal_client_id',
        'paypal_client_secret',
        'paypal_mode',
        'nfse_ativo',
        'nfse_ambiente',
        'nfse_provedor',
        'nfse_client_id',
        'nfse_client_secret',
        'nfse_inscricao_municipal',
        'nfse_municipio_codigo',
        'nfse_codigo_servico',
        'nfse_cnae',
        'nfse_descricao_servico',
        'nfse_aliquota',
        'nfse_tributacao_issqn',
        'nfse_tomador_numero_padrao',
        'nfse_tomador_bairro_padrao',
        'nfse_ultima_importacao_em',
        'reserva_escolher_empresa',
        'reserva_selecionar_modelo',
        'reserva_atraso_max_minutos',
        'reserva_bloqueio_pos_reserva_horas',
        'adicionar_novas_marcas_automaticamente',
        'adicionar_novos_modelos_automaticamente',
        'empresa_id',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'site_apresentacao_textos' => 'array',
        'site_apresentacao_imagens' => 'array',
        'site_apresentacao_cores' => 'array',
        'site_apresentacao_imagens_config' => 'array',
        'aluguel_app_documentos_obrigatorios' => 'array',
        'aluguel_app_campos_personalizados' => 'array',
        'nfse_ultima_importacao_em' => 'datetime',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function planos(): HasMany
    {
        return $this->hasMany(EmpresaPlano::class, 'empresa_id')->latest('id');
    }

    public function planoAtivo(): HasOne
    {
        return $this->hasOne(EmpresaPlano::class, 'empresa_id')
            ->whereIn('status', ['ativo', 'trial', 'aguardando_pagamento', 'inadimplente', 'cancelado'])
            ->latestOfMany('id');
    }

    public function pagamentosPlano()
    {
        return $this->hasMany(EmpresaPlanoPagamento::class, 'empresa_id');
    }

    public function arquivosRastreados()
    {
        return $this->hasMany(ArquivoRastreado::class, 'empresa_id');
    }

    public function onboardingStep()
    {
        return $this->hasOne(OnboardingStep::class, 'empresa_id');
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

    public function getStorageUsedAttribute(): string
    {
        $bytes = $this->storage_used_bytes;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2, ',', '.') . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0, ',', '.') . ' KB';
        }
        return $bytes . ' B';
    }

    public function getStorageUsedBytesAttribute(): int
    {
        return (int) $this->arquivosRastreados()->sum('size_bytes');
    }

    public function getStorageLimitBytesAttribute(): int
    {
        $plano = $this->planoAtivo?->plano;
        if ($plano && $plano->limite_armazenamento_mb > 0) {
            return (int) $plano->limite_armazenamento_mb * 1048576;
        }
        return 1073741824;
    }

    public function getStoragePercentAttribute(): int
    {
        $limit = $this->storage_limit_bytes;
        if ($limit <= 0) return 0;
        return (int) round(($this->storage_used_bytes / $limit) * 100);
    }

    public function getStorageFormattedAttribute(): string
    {
        $used = $this->storage_used;
        $limit = $this->storage_limit_bytes;
        if ($limit >= 1073741824) {
            return $used . ' / ' . number_format($limit / 1073741824, 2, ',', '.') . ' GB';
        }
        if ($limit >= 1048576) {
            return $used . ' / ' . number_format($limit / 1048576, 2, ',', '.') . ' MB';
        }
        return $used;
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

    protected function nfseClientSecret(): Attribute
    {
        return $this->encryptedStringAttribute('nfse_client_secret');
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
