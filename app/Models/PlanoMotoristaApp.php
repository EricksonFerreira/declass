<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Opcional;

class PlanoMotoristaApp extends Model
{
    use HasFactory;

    protected $table = 'planos_motorista_app';

    protected $fillable = [
        'empresa_id',
        'nome',
        'valor_periodo',
        'periodo',
        'km_tipo',
        'km_por_periodo',
        'calcao',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'valor_periodo' => 'decimal:2',
        'calcao' => 'decimal:2',
        'km_por_periodo' => 'integer',
        'ordem' => 'integer',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function opcionais(): BelongsToMany
    {
        return $this->belongsToMany(
            Opcional::class,
            'plano_motorista_app_opcionais',
            'plano_motorista_app_id',
            'opcional_id'
        )->withTimestamps()->orderBy('nome');
    }

    public function gruposFrota(): BelongsToMany
    {
        return $this->belongsToMany(
            GrupoFrota::class,
            'plano_motorista_app_grupo_frota',
            'plano_motorista_app_id',
            'grupo_frota_id'
        )->withTimestamps();
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', 'S')->orderBy('ordem')->orderBy('nome');
    }

    public function getPeriodoLabelAttribute(): string
    {
        return match ($this->periodo) {
            'semana' => 'Semana',
            'quinze' => 'Quinze dias',
            'mes' => 'Mês',
            'trimestre' => 'Trimestre',
            'semestre' => 'Semestre',
            'ano' => 'Ano',
            default => ucfirst((string) $this->periodo),
        };
    }

    public function getKmTipoLabelAttribute(): string
    {
        return $this->km_tipo === 'limitado' ? 'Limitado' : 'Livre';
    }

    public function getKmResumoAttribute(): string
    {
        if ($this->km_tipo !== 'limitado') {
            return 'Km livre';
        }

        return $this->km_por_periodo
            ? number_format((int) $this->km_por_periodo, 0, ',', '.') . ' km por período'
            : 'Km limitado';
    }
}
