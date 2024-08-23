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
}
