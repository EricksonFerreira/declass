<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * HorarioTurma: uma aula posicionada na grade de uma turma —
 * disciplina (ofertada), professor opcional e sala, dentro de um
 * slot da matriz de horarios da escola/turno.
 */
class HorarioTurma extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'horarios_turma';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'turma_id',
        'dia_semana',
        'ordem',
        'disciplina_id',
        'user_id',
        'sala',
    ];

    protected $casts = [
        'dia_semana' => 'integer',
        'ordem' => 'integer',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
