<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Veiculo da frota de transporte de uma escola.
 */
class Veiculo extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'veiculos';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'placa',
        'modelo',
        'ano',
        'capacidade',
        'motorista_nome',
        'motorista_telefone',
        'ativo',
    ];

    protected $casts = [
        'ano' => 'integer',
        'capacidade' => 'integer',
    ];

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function rotas(): HasMany
    {
        return $this->hasMany(RotaTransporte::class, 'veiculo_id');
    }

    public function passageiros(): int
    {
        return TransporteAluno::query()
            ->whereHas('rota', fn ($q) => $q->where('veiculo_id', $this->id))
            ->where('ativo', 'S')
            ->count();
    }

    /** Atalho de leitura em Blade: $model->passageiros */
    public function getPassageirosAttribute()
    {
        return $this->passageiros();
    }

    public function estaAtivo(): bool
    {
        return $this->ativo === 'S';
    }

    /** Atalho de leitura em Blade: $model->esta_ativo */
    public function getEstaAtivoAttribute()
    {
        return $this->estaAtivo();
    }
}
