<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class Empresa extends Model
{
    use HasAuditColumns, HasFactory;

    public const DEFAULT_TEMPLATE_SLUG = 'empresa-padrao';

    protected $table = 'empresas';

    protected $fillable = [
        'nome',
        'slug',
        'ativo',
        'cnpj',
        'email',
        'telefone',
        'representante',
        'endereco',
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
        'licitacoes_ativo',
        'regime_emissao',
        'certificado_path',
        'certificado_senha',
        'fiscal_token',
        'fiscal_client_id',
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
        'certificado_senha' => 'encrypted',
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
            // Trial so libera acesso dentro do prazo — vencido, a empresa
            // fica sem plano ativo e precisa contratar de fato.
            ->where(function ($q) {
                $q->where('status', '<>', 'trial')
                    ->orWhereNull('data_vencimento')
                    ->orWhere('data_vencimento', '>=', now()->startOfDay());
            })
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

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::url($path);
    }

    public function getStorageUsedAttribute(): string
    {
        $bytes = $this->storage_used_bytes;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2, ',', '.').' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.').' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0, ',', '.').' KB';
        }

        return $bytes.' B';
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
        if ($limit <= 0) {
            return 0;
        }

        return (int) round(($this->storage_used_bytes / $limit) * 100);
    }

    public function getStorageFormattedAttribute(): string
    {
        $used = $this->storage_used;
        $limit = $this->storage_limit_bytes;
        if ($limit >= 1073741824) {
            return $used.' / '.number_format($limit / 1073741824, 2, ',', '.').' GB';
        }
        if ($limit >= 1048576) {
            return $used.' / '.number_format($limit / 1048576, 2, ',', '.').' MB';
        }

        return $used;
    }

    protected function stripeSecret(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $raw = $this->getRawOriginal('stripe_secret');

                if ($raw === null || $raw === '') {
                    return null;
                }

                try {
                    return Crypt::decryptString($raw);
                } catch (DecryptException $e) {
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

    protected function mercadoLivreAccessToken(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $raw = $this->getRawOriginal('mercado_livre_access_token');

                if ($raw === null || $raw === '') {
                    return null;
                }

                try {
                    return Crypt::decryptString($raw);
                } catch (DecryptException $e) {
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

    protected function paypalClientSecret(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $raw = $this->getRawOriginal('paypal_client_secret');

                if ($raw === null || $raw === '') {
                    return null;
                }

                try {
                    return Crypt::decryptString($raw);
                } catch (DecryptException $e) {
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

    protected function nfseClientSecret(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $raw = $this->getRawOriginal('nfse_client_secret');

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
