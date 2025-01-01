<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusReserva extends Model
{
    use HasFactory;
    protected $table = 'status_reserva';
    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
        'cor',
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'status');
    }
}
