<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evento extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'user_id',
        'titulo',
        'descricao',
        'tipo',
        'data_inicio',
        'data_fim',
        'hora_inicio',
        'hora_fim',
        'local',
        'publico',
        'status',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public const TIPOS = [
        'reuniao' => 'Reunião',
        'evento' => 'Evento escolar',
        'formatura' => 'Formatura',
        'feriado' => 'Feriado / ponto facultativo',
        'avaliacao' => 'Período de avaliação',
        'prazo' => 'Prazo / entrega',
        'outro' => 'Outro',
    ];

    public const PUBLICOS = [
        'interno' => 'Somente staff',
        'todos' => 'Staff e famílias',
    ];

    public const STATUS = [
        'agendado' => 'Agendado',
        'realizado' => 'Realizado',
        'cancelado' => 'Cancelado',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    public function statusLabel(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /** Periodo coberto pelo evento (fim = inicio quando nao informado). */
    public function periodoFim()
    {
        return $this->data_fim ?? $this->data_inicio;
    }

    /** Eventos visiveis no portal: publico "todos", nao cancelados,
     *  que comecaram ate hoje ou terminam no futuro. */
    public function visivelNoPortal(): bool
    {
        return $this->publico === 'todos'
            && $this->status !== 'cancelado'
            && $this->periodoFim()->gte(now()->startOfDay());
    }
}
