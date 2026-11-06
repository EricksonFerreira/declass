<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Rota de transporte escolar: veiculo opcional, horarios
 * de ida/volta e paradas ordenadas.
 */
class RotaTransporte extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'rotas_transporte';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'veiculo_id',
        'nome',
        'horario_ida',
        'horario_volta',
        'observacoes',
        'ativo',
    ];

    protected $casts = [
        'horario_ida' => 'datetime:H:i',
        'horario_volta' => 'datetime:H:i',
    ];

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function paradas(): HasMany
    {
        return $this->hasMany(ParadaTransporte::class, 'rota_id')->orderBy('ordem');
    }

    public function passageiros(): HasMany
    {
        return $this->hasMany(TransporteAluno::class, 'rota_id');
    }

    public function passageirosAtivos(): int
    {
        return $this->passageiros()->where('ativo', 'S')->count();
    }

    public function estaAtivo(): bool
    {
        return $this->ativo === 'S';
    }
}
