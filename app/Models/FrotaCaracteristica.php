<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrotaCaracteristica extends Model
{
    use HasFactory;

    protected $table = 'frota_caracteristica';
    protected $fillable = [
        'nome',
        'ativo',
    ];
}
