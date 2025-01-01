<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusMovimentacao extends Model
{
    use HasFactory;

    protected $table = 'status_movimentacao';
    protected $fillable = [
        'nome',
        'ativo',
    ];
}
