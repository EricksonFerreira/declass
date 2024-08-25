<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaracteristicaGrupoFrota extends Model
{
    use HasFactory;

    protected $table = 'caracteristica_grupo_frota';
    protected $fillable = [
        'nome',
        'ativo',
    ];

    public function gruposFrota()
    {
        return $this->belongsToMany(GrupoFrota::class, 'grupo_frota_caracteristica_frota',  'caracteristica_frota_id', 'grupo_frota_id')
            ->withPivot('valor');
    }
}
