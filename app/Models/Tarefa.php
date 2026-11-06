<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tarefa/dever de casa de uma turma, opcionalmente por disciplina.
 */
class Tarefa extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    public const STATUS_ENTREGA = ['pendente', 'entregue', 'avaliada'];

    protected $fillable = [
        'empresa_id',
        'turma_id',
        'disciplina_id',
        'user_id',
        'titulo',
        'descricao',
        'instrucoes',
        'data_entrega',
        'nota_maxima',
        'ativo',
    ];

    protected $casts = [
        'data_entrega' => 'date',
        'nota_maxima' => 'decimal:2',
    ];

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(EntregaTarefa::class, 'tarefa_id');
    }

    public function atraso(): bool
    {
        return $this->data_entrega->isPast() && $this->ativo === 'S';
    }

    public function entregasEntregues(): int
    {
        return $this->entregas()->whereIn('status', ['entregue', 'avaliada'])->count();
    }

    public function entregasTotal(): int
    {
        return $this->entregas()->count();
    }

    public function estaAtivo(): bool
    {
        return $this->ativo === 'S';
    }
}
