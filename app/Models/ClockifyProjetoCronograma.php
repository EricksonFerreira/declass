<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClockifyProjetoCronograma extends Model
{
    protected $table = 'clockify_projeto_cronogramas';

    protected $fillable = [
        'clockify_projeto_id',
        'nome',
        'status', // valido, invalido, rascunho
        'data_inicio',
        'data_fim',
        'horas_orcadas',
        'valor_hora',
        'valor_faturado',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'horas_orcadas' => 'float',
        'valor_hora' => 'float',
        'valor_faturado' => 'float',
    ];

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(ClockifyProjeto::class, 'clockify_projeto_id');
    }

    public function planejamentos(): HasMany
    {
        return $this->hasMany(ClockifyProjetoPlanejamento::class, 'clockify_projeto_cronograma_id');
    }

    public function getHorasPlanejadasAttribute(): float
    {
        return (float) $this->planejamentos()->sum('horas');
    }

    public function getDiasUteisPlanejamentoAttribute(): int
    {
        return (int) $this->planejamentos()->where('horas', '>', 0)->count();
    }

    public function getCustoPlanejadoAttribute(): float
    {
        return $this->horas_planejadas * ($this->valor_hora ?? 0.0);
    }

    public function getCustoOrcadoAttribute(): float
    {
        return ($this->horas_orcadas ?? 0.0) * ($this->valor_hora ?? 0.0);
    }
}
