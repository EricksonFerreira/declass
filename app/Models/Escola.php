<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Escola/unidade de ensino. Nivel 2 da arquitetura multi-tenant:
 * pertence diretamente a um tenant (empresa).
 */
class Escola extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'nome',
        'slug',
        'cnpj',
        'email',
        'telefone',
        'endereco',
        'cidade',
        'estado',
        'capacidade_alunos',
        'turno_padrao',
        'logo_path',
        'cor_primaria',
        'cor_secundaria',
        'ativa',
    ];

    public const TURNOS = [
        'matutino' => 'Matutino',
        'vespertino' => 'Vespertino',
        'noturno' => 'Noturno',
        'integral' => 'Integral',
    ];

    protected static function booted(): void
    {
        static::creating(function (Escola $escola): void {
            if (empty($escola->slug)) {
                $escola->slug = static::gerarSlugUnico($escola->empresa_id, $escola->nome);
            }
        });

        static::updating(function (Escola $escola): void {
            if ($escola->isDirty('nome') && ! $escola->isDirty('slug')) {
                $escola->slug = static::gerarSlugUnico($escola->empresa_id, $escola->nome, $escola->id);
            }
        });
    }

    public static function gerarSlugUnico(?int $empresaId, string $nome, ?int $ignoreId = null): string
    {
        $base = Str::slug($nome) ?: 'escola';
        $slug = $base;
        $i = 1;

        while (static::query()
            ->where('empresa_id', $empresaId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_escolas', 'escola_id', 'user_id')
            ->withTimestamps();
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
        return strtoupper((string) $this->ativa) === 'S';
    }

    /** Atalho de leitura em Blade: $model->esta_ativa */
    public function getEstaAtivaAttribute()
    {
        return $this->estaAtiva();
    }
}
