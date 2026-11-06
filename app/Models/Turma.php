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
 * Turma: alunos de uma escola, em um ano letivo, cursando
 * uma etapa. Base para matriculas, diario e avaliacoes.
 */
class Turma extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'ano_letivo_id',
        'etapa_id',
        'nome',
        'turno',
        'sala',
        'capacidade_maxima',
        'ativo',
    ];

    protected $casts = [
        'capacidade_maxima' => 'integer',
    ];

    public const TURNOS = [
        'matutino' => 'Matutino',
        'vespertino' => 'Vespertino',
        'noturno' => 'Noturno',
        'integral' => 'Integral',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function anoLetivo(): BelongsTo
    {
        return $this->belongsTo(AnoLetivo::class, 'ano_letivo_id');
    }

    public function etapa(): BelongsTo
    {
        return $this->belongsTo(Etapa::class, 'etapa_id');
    }

    public function disciplinas(): BelongsToMany
    {
        return $this->belongsToMany(Disciplina::class, 'turma_disciplina')
            ->withPivot('carga_horaria_semanal')
            ->withTimestamps();
    }

    public function atribuicoes(): HasMany
    {
        return $this->hasMany(Atribuicao::class, 'turma_id');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'turma_id');
    }

    public function estaAtiva(): bool
    {
        return strtoupper((string) $this->ativo) === 'S';
    }
}
