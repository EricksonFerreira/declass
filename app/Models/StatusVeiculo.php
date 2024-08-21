<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusVeiculo extends Model
{
    use HasFactory;

    protected $table = 'status_veiculos';
    protected $fillable = [
        'nome',
        'ativo',
    ];
}
