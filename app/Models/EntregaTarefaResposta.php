<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Resposta do aluno a uma questao dentro da sua entrega.
 * 'correta' e preenchida automaticamente em objetivas.
 */
class EntregaTarefaResposta extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'entrega_tarefa_respostas';

    protected $fillable = [
        'empresa_id',
        'entrega_id',
        'questao_id',
        'resposta',
        'correta',
    ];

    protected $casts = [
        'correta' => 'boolean',
    ];

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(EntregaTarefa::class, 'entrega_id');
    }

    public function questao(): BelongsTo
    {
        return $this->belongsTo(TarefaQuestao::class, 'questao_id');
    }
}
