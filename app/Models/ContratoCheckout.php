<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContratoCheckout extends Model
{
    protected $table = 'contrato_checkout';

    protected $fillable = [
        'reserva_id',
        'veiculo_id',
        'empresa_id',
        'quilometragem_devolucao',
        'nivel_combustivel_devolucao',
        'data_devolucao',
        'hora_devolucao',
        'notas_devolucao',
        'observacoes_devolucao',
        'foto_direita_path',
        'foto_esquerda_path',
        'foto_frente_path',
        'foto_tras_path',
        'foto_velocimetro_path',
        'foto_combustivel_path',
        'assinatura',
        'data_assinatura',
    ];

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }
}
