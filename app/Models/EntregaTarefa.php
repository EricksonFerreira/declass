<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entrega de uma tarefa por matricula (um aluno por tarefa).
 */
class EntregaTarefa extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'entregas_tarefa';

    public const STATUS = [
        'pendente' => 'Pendente',
        'entregue' => 'Entregue',
        'avaliada' => 'Avaliada',
    ];

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'tarefa_id',
        'matricula_id',
        'status',
        'nota',
        'data_entrega',
        'observacoes',
    ];

    protected $casts = [
        'nota' => 'decimal:2',
        'data_entrega' => 'date',
    ];

    public function tarefa(): BelongsTo
    {
        return $this->belongsTo(Tarefa::class, 'tarefa_id');
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function statusLabel(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /**
     * Situacao derivada: pendente com data de entrega vencida = atrasada.
     */
    public function situacao(): string
    {
        if ($this->status === 'pendente'
            && $this->tarefa
            && $this->tarefa->data_entrega->isPast()) {
            return 'atrasada';
        }

        return $this->status;
    }

    public function situacaoLabel(): string
    {
        return [
            'pendente' => 'Pendente',
            'entregue' => 'Entregue',
            'avaliada' => 'Avaliada',
            'atrasada' => 'Atrasada',
        ][$this->situacao()] ?? $this->situacao();
    }
}
