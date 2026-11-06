<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Aviso/comunicado publicado pela escola para as familias.
 * turma_id nulo significa aviso geral da escola.
 */
class Aviso extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'turma_id',
        'titulo',
        'mensagem',
        'tipo',
        'data_publicacao',
        'ativo',
    ];

    protected $casts = [
        'data_publicacao' => 'date',
    ];

    public const TIPOS = [
        'info' => 'Informativo',
        'urgente' => 'Urgente',
        'evento' => 'Evento',
    ];

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function estaAtivo(): bool
    {
        return $this->ativo === 'S';
    }

    public function ehGeral(): bool
    {
        return $this->turma_id === null;
    }
}
