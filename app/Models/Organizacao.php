<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Organizacao: mantenedora ou rede educacional dentro de um tenant.
 * Nivel 2 da arquitetura multi-tenant.
 */
class Organizacao extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'organizacoes';

    protected $fillable = [
        'empresa_id',
        'nome',
        'tipo',
        'cnpj',
        'email',
        'telefone',
        'endereco',
        'ativo',
    ];

    public const TIPOS = [
        'mantenedora' => 'Mantenedora',
        'rede' => 'Rede de Ensino',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function escolas(): HasMany
    {
        return $this->hasMany(Escola::class, 'organizacao_id');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_organizacoes', 'organizacao_id', 'user_id')
            ->withTimestamps();
    }

    public function estaAtiva(): bool
    {
        return strtoupper((string) $this->ativo) === 'S';
    }
}
