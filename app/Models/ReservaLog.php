<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservaLog extends Model
{
    protected $table = 'reserva_logs';

    protected $fillable = ['reserva_id', 'user_id', 'acao', 'nivel', 'detalhes'];

    protected $casts = ['created_at' => 'datetime'];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
