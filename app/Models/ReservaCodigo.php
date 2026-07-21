<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservaCodigo extends Model
{
    use HasFactory;

    protected $table = 'reserva_codigos';

    protected $fillable = [
        'empresa_id',
        'codigo',
        'tipo_origem',
        'reserva_id',
        'reserva_motorista_app_id',
        'status_label',
        'data_inicio',
        'data_fim',
        'valor_total',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'valor_total' => 'decimal:2',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function reservaMotoristaApp(): BelongsTo
    {
        return $this->belongsTo(ReservaMotoristaApp::class, 'reserva_motorista_app_id');
    }
}
