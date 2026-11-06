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
        'ativo',
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
        $path = is_string($this->logo_path) ? trim($this->logo_path) : '';

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::url($path);
    }

    public function estaAtiva(): bool
    {
        return strtoupper((string) $this->ativo) === 'S';
    }
}
