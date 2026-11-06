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
        'destino',
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

    /** Publico do aviso: familias (portal) ou equipe (mural interno). */
    public const DESTINOS = [
        'familias' => 'Famílias (portal)',
        'staff' => 'Equipe (mural interno)',
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
