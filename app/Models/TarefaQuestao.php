<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Questao de uma atividade/prova lancada pelo professor:
 * discursiva (resposta aberta) ou objetiva (alternativas
 * A-E com gabarito para correcao automatica).
 */
class TarefaQuestao extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'tarefa_questoes';

    protected $fillable = [
        'empresa_id',
        'tarefa_id',
        'ordem',
        'tipo',
        'enunciado',
        'alternativas',
        'gabarito',
        'valor',
    ];

    protected $casts = [
        'alternativas' => 'array',
        'valor' => 'decimal:2',
    ];

    public const TIPOS = [
        'objetiva' => 'Objetiva (A–E)',
        'discursiva' => 'Discursiva',
    ];

    public function tarefa(): BelongsTo
    {
        return $this->belongsTo(Tarefa::class, 'tarefa_id');
    }

    public function ehObjetiva(): bool
    {
        return $this->tipo === 'objetiva';
    }
}
