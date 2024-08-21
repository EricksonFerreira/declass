<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frota extends Model
{
    use HasFactory;

    protected $table = 'frotas';
    protected $fillable = [
        'nome',
        'ativo',
    ];
}
