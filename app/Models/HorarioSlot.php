<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * HorarioSlot: uma aula da matriz de horarios de uma escola,
 * em um turno, num dia da semana e numa posicao (ordem).
 * Define as faixas de horario usadas pela grade das turmas.
 */
class HorarioSlot extends Model
{
    use HasAuditColumns;

    protected $table = 'horario_slots';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'horario_id',
        'turno',
        'dia_semana',
        'ordem',
        'hora_inicio',
        'hora_fim',
    ];

    protected $casts = [
        'dia_semana' => 'integer',
        'ordem' => 'integer',
    ];

    public const DIAS = [
        1 => 'Segunda',
        2 => 'Terça',
        3 => 'Quarta',
        4 => 'Quinta',
        5 => 'Sexta',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }

    public static function diaLabel(int $dia): string
    {
        return self::DIAS[$dia] ?? (string) $dia;
    }
}
