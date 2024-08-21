<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaUtil extends Model
{
    use HasFactory;

    protected $table = 'dias_uteis';
    protected $fillable = [
        'dia',
        'nome',
        'ativo',
    ];

    public function estacoes()
    {
        return $this->belongsToMany(Estacao::class, 'estacoes_dias_uteis', 'dia_util_id', 'estacao_id');
    }
}
