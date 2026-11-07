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
        'periodicidade',
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

    /** Classe de badge Bootstrap por status (letivo verde, encerrado vermelho, planejamento azul). */
    public const STATUS_BADGES = [
        'letivo' => 'success',
        'encerrado' => 'danger',
        'planejamento' => 'info',
    ];

    /** Classe de badge Bootstrap por periodicidade. */
    public const PERIODICIDADE_BADGES = [
        'bimestral' => 'primary',
        'trimestral' => 'info',
        'semestral' => 'warning',
        'anual' => 'dark',
        'personalizado' => 'secondary',
    ];

    /**
     * Periodicidade: quantidade e nomes dos periodos letivos gerados
     * automaticamente. 'personalizado' nao gera — os periodos sao
     * criados manualmente em Periodos Letivos.
     */
    public const PERIODICIDADES = [
        'bimestral' => 'Bimestral (4 períodos)',
        'trimestral' => 'Trimestral (3 períodos)',
        'semestral' => 'Semestral (2 períodos)',
        'anual' => 'Anual (1 período)',
        'personalizado' => 'Personalizado (gerar manualmente)',
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

    public function periodos(): HasMany
    {
        return $this->hasMany(PeriodoLetivo::class, 'ano_letivo_id');
    }

    public function estaAtivo(): bool
    {
        return strtoupper((string) $this->ativo) === 'S';
    }
}
