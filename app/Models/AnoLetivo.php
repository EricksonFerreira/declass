<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Ano letivo de uma escola/unidade (ex.: 2026).
 */
class AnoLetivo extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'anos_letivos';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'ano',
        'data_inicio',
        'data_fim',
        'status',
        'ativo',
    ];

    protected $casts = [
        'ano' => 'integer',
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public const STATUS = [
        'letivo' => 'Letivo',
        'encerrado' => 'Encerrado',
        'planejamento' => 'Planejamento',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class, 'ano_letivo_id');
    }

    public function estaAtivo(): bool
    {
        return strtoupper((string) $this->ativo) === 'S';
    }
}
