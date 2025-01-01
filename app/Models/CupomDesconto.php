<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CupomDesconto extends Model
{
    use HasFactory;

    protected $table = 'cupom_desconto';
    protected $fillable = [
        'nome',
        'codigo',
        'valor',
        'ativo',
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'cupom_desconto_id');
    }
}
