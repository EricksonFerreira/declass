<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feriado extends Model
{
    use HasFactory;

    protected $table = 'feriados';

    protected $fillable = [
        'empresa_id',
        'estacao_id',
        'data',
        'nome',
        'ativo',
    ];

    protected $dates = [
        'data',
    ];

    public function estacao()
    {
        return $this->belongsTo(Estacao::class, 'estacao_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
