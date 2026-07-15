<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Models\OnboardingStep;

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
        'aluguel_app_ativo',
        'aluguel_app_exigir_aprovacao_documentos',
        'aluguel_app_documentos_obrigatorios',
        'aluguel_app_documento_adicional_label',
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
    ];

    protected $casts = [
        'site_apresentacao_textos' => 'array',
        'site_apresentacao_imagens' => 'array',
        'site_apresentacao_cores' => 'array',
        'site_apresentacao_imagens_config' => 'array',
        'aluguel_app_documentos_obrigatorios' => 'array',
        'nfse_ultima_importacao_em' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function planos()
    {
        return $this->hasMany(EmpresaPlano::class, 'empresa_id');
    }

    public function planoAtivo()
    {
        return $this->hasOne(EmpresaPlano::class, 'empresa_id')
            ->whereIn('status', ['ativo', 'trial', 'inadimplente', 'aguardando_pagamento'])
            ->latest();
    }

    public function pagamentosPlano()
    {
        return $this->hasMany(EmpresaPlanoPagamento::class, 'empresa_id');
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
        $totalBytes = 0;
        $disk = Storage::disk('public');

        if ($this->logo_path && $disk->exists($this->logo_path)) {
            $totalBytes += $disk->size($this->logo_path);
        }

        $siteImages = $this->site_apresentacao_imagens ?? [];
        if (is_array($siteImages)) {
            foreach ($siteImages as $area => $path) {
                if (is_string($path) && $disk->exists($path)) {
                    $totalBytes += $disk->size($path);
                }
            }
        }

        $reservaIds = \App\Models\Reserva::where('empresa_id', $this->id)->pluck('id');
        if ($reservaIds->isNotEmpty()) {
            $checkins = \App\Models\ContratoCheckin::whereIn('reserva_id', $reservaIds)->get();
            foreach ($checkins as $ci) {
                foreach (['foto_direita_path','foto_esquerda_path','foto_frente_path','foto_tras_path','foto_velocimetro_path','foto_combustivel_path'] as $col) {
                    if ($ci->$col && $disk->exists($ci->$col)) $totalBytes += $disk->size($ci->$col);
                }
                if ($ci->assinatura && $disk->exists('assinaturas/' . $ci->assinatura)) $totalBytes += $disk->size('assinaturas/' . $ci->assinatura);
                if ($ci->pdf_path && $disk->exists($ci->pdf_path)) $totalBytes += $disk->size($ci->pdf_path);
            }
            $checkouts = \App\Models\ContratoCheckout::whereIn('reserva_id', $reservaIds)->get();
            foreach ($checkouts as $co) {
                foreach (['foto_direita_path','foto_esquerda_path','foto_frente_path','foto_tras_path','foto_velocimetro_path','foto_combustivel_path'] as $col) {
                    if ($co->$col && $disk->exists($co->$col)) $totalBytes += $disk->size($co->$col);
                }
            }
            $danoImagens = \App\Models\VeiculoDanosImagem::whereHas('dano', fn($q) => $q->whereIn('reserva_id', $reservaIds))->get();
            foreach ($danoImagens as $img) {
                if ($img->caminho_imagem && $disk->exists($img->caminho_imagem)) $totalBytes += $disk->size($img->caminho_imagem);
            }
            foreach (\App\Models\Multa::whereIn('reserva_id', $reservaIds)->get() as $m) {
                foreach (['anexo_comprovante_path','anexo_pdf_path'] as $col) { if ($m->$col && $disk->exists($m->$col)) $totalBytes += $disk->size($m->$col); }
            }
            foreach (\App\Models\Sinistro::whereIn('reserva_id', $reservaIds)->get() as $s) {
                foreach (['anexo_bo_path','anexo_foto1_path','anexo_foto2_path','anexo_foto3_path','anexo_laudo_path'] as $col) { if ($s->$col && $disk->exists($s->$col)) $totalBytes += $disk->size($s->$col); }
            }
        }

        foreach (\App\Models\GrupoFrota::where('empresa_id', $this->id)->get() as $g) {
            if ($g->imagem && $disk->exists($g->imagem)) $totalBytes += $disk->size($g->imagem);
            $imagens = is_string($g->imagens) ? json_decode($g->imagens, true) : (($g->imagens ?? []) ?: []);
            foreach ((array) $imagens as $imgPath) { if (is_string($imgPath) && $disk->exists($imgPath)) $totalBytes += $disk->size($imgPath); }
        }
        foreach (\App\Models\CaracteristicaFrota::where('empresa_id', $this->id)->get() as $c) {
            if ($c->imagem && $disk->exists($c->imagem)) $totalBytes += $disk->size($c->imagem);
        }
        foreach (\App\Models\Modelo::where('empresa_id', $this->id)->get() as $m) {
            if ($m->imagem && $disk->exists($m->imagem)) $totalBytes += $disk->size($m->imagem);
        }

        return $totalBytes;
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
