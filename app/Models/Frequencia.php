<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Presenca de um aluno em uma aula do diario.
 */
class Frequencia extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    public const STATUS = [
        'presente' => 'Presente',
        'falta' => 'Falta',
        'justificada' => 'Falta justificada',
    ];

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'diario_id',
        'matricula_id',
        'status',
        'justificativa',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function diario(): BelongsTo
    {
        return $this->belongsTo(Diario::class, 'diario_id');
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
}
