<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaDano extends Model
{
    use HasFactory;

    protected $table = 'categoria_danos';
    protected $fillable = [
        'nome',
        'ativo',
    ];

    public function danos()
    {
        return $this->hasMany(Dano::class, 'categoria_id');
    }
}
