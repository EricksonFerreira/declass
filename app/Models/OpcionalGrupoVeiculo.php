<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpcionalGrupoVeiculo extends Model
{
    use HasFactory;

    protected $table = 'opcional_grupo_veiculo';
    protected $fillable = [
        'nome',
        'ativo',
    ];
}
