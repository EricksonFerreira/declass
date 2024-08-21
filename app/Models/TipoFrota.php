<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoFrota extends Model
{
    use HasFactory;

    protected $table = 'tipo_frota';
    protected $fillable = [
        'codigo',
        'nome',
        'ativo',
    ];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
    ];

    public function caracteristicas()
    {
        return $this->belongsToMany(CaracteristicaFrota::class, 'caracteristicas_tipo_frota', 'tipo_frota_id', 'caracteristica_frota_id');
    }
}
