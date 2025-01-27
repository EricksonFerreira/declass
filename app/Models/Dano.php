<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dano extends Model
{
    use HasFactory;

    protected $table = 'danos';
    protected $fillable = [
        'nome',
        'ativo',
        'categoria_id',
    ];

    public function veiculoDanos()
    {
        return $this->hasMany(VeiculoDanos::class, 'dano_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaDano::class, 'categoria_id');
    }
}
