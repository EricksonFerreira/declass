<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CupomDesconto extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'cupom_desconto';
    protected $fillable = [
        'nome',
        'codigo',
        'valor',
        'ativo',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'cupom_desconto_id');
    }
}
