<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Etapa de ensino configuravel pelo tenant
 * (educacao infantil, fundamental, medio, EJA...).
 */
class Etapa extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'nome',
        'segmento',
        'ordem',
        'descricao',
        'emite_certificado',
        'ativo',
    ];

    protected $casts = [
        'ordem' => 'integer',
    ];

    public const SEGMENTOS = [
        'infantil' => 'Educação Infantil',
        'fundamental' => 'Ensino Fundamental',
        'medio' => 'Ensino Médio',
        'eja' => 'EJA',
        'superior' => 'Superior',
        'outro' => 'Outro',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class, 'etapa_id');
    }

    public function estaAtiva(): bool
    {
        return strtoupper((string) $this->ativo) === 'S';
    }
}
