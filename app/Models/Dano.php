<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dano extends Model
{
    use HasFactory, HasAuditColumns;

    protected $table = 'danos';
    protected $fillable = [
        'nome',
        'ativo',
        'categoria_id',
        'localizacao',
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
