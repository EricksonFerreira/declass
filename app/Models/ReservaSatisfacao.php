<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;

class ReservaSatisfacao extends Model
{
    use HasAuditColumns;

    protected $table = 'reserva_satisfacoes';

    protected $fillable = [
        'empresa_id',
        'reserva_id',
        'respondente_user_id',
        'satisfacao_geral',
        'facilidade_reserva',
        'nps_nota',
        'comentario',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'satisfacao_geral' => 'integer',
        'facilidade_reserva' => 'integer',
        'nps_nota' => 'integer',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function respondente()
    {
        return $this->belongsTo(User::class, 'respondente_user_id');
    }
}
