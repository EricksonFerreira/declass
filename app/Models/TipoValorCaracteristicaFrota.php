<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoValorCaracteristicaFrota extends Model
{
    use HasFactory;

    protected $table = 'tipo_valor_caracteristica_frota';
    protected $fillable = [
        'nome',
        'configuracao',
        'ativo',
    ];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
    ];
}
