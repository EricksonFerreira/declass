<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Tenant da plataforma SaaS escolar.
 *
 * Nivel 1 da arquitetura multi-tenant: o cliente independente da
 * plataforma. Uma empresa pode ter varias escolas/unidades.
 * Todo registro de negocio carrega empresa_id
 * e e isolado pelo escopo global 'empresa' + EmpresaContext.
 */
class Empresa extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'empresas';

    protected $fillable = [
        'nome',
        'slug',
        'dominio',
        'cnpj',
        'email',
        'telefone',
        'endereco',
        'logo_path',
        'cor_primaria',
        'cor_secundaria',
        'cor_terciaria',
        'site_titulo',
        'site_descricao',
        'site_aviso',
        'site_whatsapp',
        'site_instagram',
        'site_ativo',
        'site_unidades',
        'site_matricula',
        'site_matricula_ano',
        'site_contato',
        'site_modelo',
        'site_banner',
        'site_imagem_sobre',
        'site_galeria',
        'site_depoimentos',
        'ativo',
        'gateway_pagamento',
        'gateway_api_key',
        'gateway_ambiente',
        'gateway_webhook_token',
    ];

    protected $hidden = ['gateway_api_key'];

    /**
     * Modelos de layout disponiveis para o site publico da empresa
     * (chave => rotulo para a tela de configuracao).
     */
    public const SITE_MODELOS = [
        'classico' => 'Clássico',
        'escola' => 'Escola moderna',
        'gandolfi' => 'Tradicional',
        'hero' => 'Destaque com imagem',
        'minimal' => 'Minimalista',
    ];

    protected $casts = [
        'site_galeria' => 'array',
    ];

    /**
     * Exclusao logica: desativa a empresa e libera o slug (unique)
     * para recadastro. Dados vinculados sao preservados.
     */
    protected static function booted(): void
    {
        static::deleting(function (Empresa $empresa): void {
            if ($empresa->isForceDeleting()) {
                return;
            }

            $empresa->ativo = 'N';
            $empresa->slug = mb_substr($empresa->slug.'-excluida-'.$empresa->id, 0, 250);
            $empresa->saveQuietly();
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function escolas(): HasMany
    {
        return $this->hasMany(Escola::class, 'empresa_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(EmpresaDocumento::class, 'empresa_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'empresa_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->imagemPublicaUrl($this->logo_path);
    }

    public function getSiteBannerUrlAttribute(): ?string
    {
        return $this->imagemPublicaUrl($this->site_banner);
    }

    public function getSiteImagemSobreUrlAttribute(): ?string
    {
        return $this->imagemPublicaUrl($this->site_imagem_sobre);
    }

    public function getSiteGaleriaUrlsAttribute(): array
    {
        $paths = $this->site_galeria;
        if (is_string($paths)) {
            $paths = json_decode($paths, true) ?: [];
        }

        return collect(is_array($paths) ? $paths : [])
            ->map(fn ($path) => $this->imagemPublicaUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Depoimentos do site — um por linha no formato "Nome | Texto".
     */
    public function getDepoimentosAttribute(): array
    {
        return collect(preg_split('/\r?\n/', (string) $this->site_depoimentos))
            ->map(fn ($linha) => array_map('trim', explode('|', $linha, 2)))
            ->filter(fn ($partes) => ($partes[0] ?? '') !== '')
            ->map(fn ($partes) => ['nome' => $partes[0], 'texto' => $partes[1] ?? ''])
            ->values()
            ->all();
    }

    private function imagemPublicaUrl(?string $path): ?string
    {
        $path = is_string($path) ? trim($path) : '';

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::url($path);
    }

    public function estaAtiva(): bool
    {
        return strtoupper((string) $this->ativo) === 'S';
    }

    /**
     * Site publico da empresa ("rede de escolas") so fica no ar quando
     * a empresa esta ativa e o site foi ligado nas definicoes.
     */
    public function sitePublicoAtivo(): bool
    {
        return $this->estaAtiva() && strtoupper((string) $this->site_ativo) === 'S';
    }

    /**
     * URL publica do site da empresa: dominio personalizado quando
     * configurado, senao o path /rede/{slug} no dominio da plataforma.
     */
    public function getSiteUrlAttribute(): ?string
    {
        $dominio = trim((string) $this->dominio);

        if ($dominio !== '') {
            return 'https://'.$dominio;
        }

        return url('/rede/'.$this->slug);
    }
}
