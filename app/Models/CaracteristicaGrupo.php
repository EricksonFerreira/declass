<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaracteristicaGrupo extends Model
{
    use HasFactory;

    protected $table = 'caracteristica_grupo';
    protected $fillable = [
        'nome',
        'ativo',
    ];
}
