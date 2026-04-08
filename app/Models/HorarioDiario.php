<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioDiario extends Model
{
    use HasFactory;

    protected $table = 'horarios_diarios';
    protected $fillable = [
        'nome',
        'ativo',
        'empresa_id',
    ];

    public function estacoes()
    {
        return $this->belongsToMany(Estacao::class);
    }

}
