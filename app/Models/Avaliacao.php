<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Instrumento de avaliacao aplicado a disciplina de uma turma.
 */
class Avaliacao extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'avaliacoes';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'turma_id',
        'disciplina_id',
        'user_id',
        'tipo',
        'nome',
        'periodo',
        'periodo_letivo_id',
        'data',
        'nota_maxima',
        'descricao',
        'observacoes',
    ];

    protected $casts = [
        'data' => 'date',
        'nota_maxima' => 'float',
    ];

    public const TIPOS = [
        'prova' => 'Prova',
        'trabalho' => 'Trabalho',
        'participacao' => 'Participação',
        'simulado' => 'Simulado',
        'projeto' => 'Projeto',
        'recuperacao' => 'Recuperação',
        'outro' => 'Outro',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function periodoLetivo(): BelongsTo
    {
        return $this->belongsTo(PeriodoLetivo::class, 'periodo_letivo_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'avaliacao_id');
    }
}
