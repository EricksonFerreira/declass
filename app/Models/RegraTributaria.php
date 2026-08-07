<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegraTributaria extends Model
{
    protected $table = 'regras_tributarias';

    protected $fillable = [
        'tributo',
        'tipo_operacao',
        'aliquota',
        'data_inicio',
        'data_fim',
        'gera_credito',
        'ativo',
    ];

    protected $casts = [
        'aliquota' => 'float',
        'data_inicio' => 'date:Y-m-d',
        'data_fim' => 'date:Y-m-d',
        'gera_credito' => 'boolean',
        'ativo' => 'boolean',
    ];
}
