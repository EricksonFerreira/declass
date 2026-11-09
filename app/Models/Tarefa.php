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

    public const TIPOS = [
        'atividade' => 'Atividade',
        'prova' => 'Prova',
        'trabalho' => 'Trabalho',
    ];

    /** O que o aluno envia pelo portal (alem das questoes online). */
    public const TIPOS_ENTREGA = [
        'nenhuma' => 'Sem envio online',
        'leitura' => 'Confirmação de leitura',
        'texto' => 'Texto digitado',
        'foto' => 'Fotos/arquivos',
    ];

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'turma_id',
        'disciplina_id',
        'user_id',
        'titulo',
        'tipo',
        'descricao',
        'instrucoes',
        'data_entrega',
        'nota_maxima',
        'tipo_entrega',
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

    public function questoes(): HasMany
    {
        return $this->hasMany(TarefaQuestao::class, 'tarefa_id')->orderBy('ordem');
    }

    public function temQuestoes(): bool
    {
        return $this->questoes()->exists();
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

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->tipo] ?? self::TIPOS['atividade'];
    }

    public function tipoEntregaLabel(): string
    {
        return self::TIPOS_ENTREGA[$this->tipo_entrega] ?? self::TIPOS_ENTREGA['nenhuma'];
    }

    /** Aluno precisa enviar algo pelo portal (leitura, texto ou fotos). */
    public function exigeEntregaOnline(): bool
    {
        return ($this->tipo_entrega ?? 'nenhuma') !== 'nenhuma';
    }
}
