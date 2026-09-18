<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClockifyProjeto extends Model
{
    use HasAuditColumns;

    public const STATUS_EM_ANDAMENTO = 'em_andamento';

    public const STATUS_CONCLUIDO = 'concluido';

    public const STATUS_CANCELADO = 'cancelado';

    public const STATUS_LABELS = [
        self::STATUS_EM_ANDAMENTO => 'Em andamento',
        self::STATUS_CONCLUIDO => 'Concluído',
        self::STATUS_CANCELADO => 'Cancelado',
    ];

    protected $table = 'clockify_projetos';

    protected $fillable = [
        'empresa_id',
        'clockify_cliente_id',
        'responsavel_id',
        'codigo',
        'nome',
        'descricao',
        'cor',
        'publico',
        'ativo',
        'status',
        'horas_orcadas',
        'valor_hora',
        'valor_faturado',
        'data_entrega_previsao',
        'horas_diarias_planejamento',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'publico' => 'boolean',
        'ativo' => 'boolean',
        'horas_orcadas' => 'float',
        'valor_hora' => 'float',
        'valor_faturado' => 'float',
        'data_entrega_previsao' => 'date',
        'horas_diarias_planejamento' => 'float',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(ClockifyCliente::class, 'clockify_cliente_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    /**
     * Admin pode gerenciar qualquer projeto; responsavel apenas o proprio.
     */
    public function podeSerGerenciadoPor(User $user): bool
    {
        return app(\App\Services\VisibilidadeService::class)->podeGerenciarProjeto($user, $this);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clockify_projeto_user', 'clockify_projeto_id', 'user_id')
            ->withTimestamps();
    }

    public function trackers(): HasMany
    {
        return $this->hasMany(ClockifyTimeTracker::class, 'clockify_projeto_id');
    }

    public function cronogramas(): HasMany
    {
        return $this->hasMany(ClockifyProjetoCronograma::class, 'clockify_projeto_id');
    }

    public function estimativas(): HasMany
    {
        return $this->hasMany(ClockifyProjetoEstimativa::class, 'clockify_projeto_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? self::STATUS_LABELS[self::STATUS_EM_ANDAMENTO];
    }

    public function getStatusCorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CONCLUIDO => 'success',
            self::STATUS_CANCELADO => 'secondary',
            default => 'warning',
        };
    }

    public function getCronogramaAtivoAttribute(): ?ClockifyProjetoCronograma
    {
        return $this->cronogramas()->where('status', 'valido')->first();
    }

    public function getDiasUteisPlanejamentoAttribute(): int
    {
        $cron = $this->cronograma_ativo;

        return $cron ? $cron->dias_uteis_planejamento : 0;
    }

    public function getHorasPlanejadasAttribute(): float
    {
        $cron = $this->cronograma_ativo;

        return $cron ? $cron->horas_planejadas : 0.0;
    }

    public function getCustoPlanejadoAttribute(): float
    {
        $cron = $this->cronograma_ativo;

        return $cron ? $cron->custo_planejado : 0.0;
    }

    public function getCustoOrcadoAttribute(): float
    {
        return ($this->horas_orcadas ?? 0.0) * ($this->valor_hora ?? 0.0);
    }

    public function getTotalHorasTrabalhadasAttribute(): float
    {
        // Sum duration in seconds of all trackers and convert to hours
        $seconds = $this->trackers->sum('duration_in_seconds');

        return $seconds / 3600;
    }

    public function getCustoRealAttribute(): float
    {
        return ($this->total_horas_trabalhadas ?? 0) * ($this->valor_hora ?? 0);
    }

    public function getRentabilidadeAttribute(): float
    {
        return ($this->valor_faturado ?? 0) - $this->custo_real;
    }
}
