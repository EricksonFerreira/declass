<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamadoStatus extends Model
{
    protected $fillable = ['empresa_id', 'nome', 'slug', 'cor', 'ordem', 'ativo', 'padrao_entrada', 'padrao_atendimento', 'padrao_fechamento'];

    protected $casts = [
        'ativo' => 'boolean',
        'padrao_entrada' => 'boolean',
        'padrao_atendimento' => 'boolean',
        'padrao_fechamento' => 'boolean',
    ];
}
