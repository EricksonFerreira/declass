<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoFrota extends Model
{
    use HasFactory;

    protected $table = 'grupos_frota';
    protected $fillable = [
        'tipo_frota_id',
        'codigo',
        'codigo_sipp',
        'nome',
        'marca_id',
        'imagem',
        'ativo',
    ];
}
