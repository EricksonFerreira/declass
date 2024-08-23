<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoOpcional extends Model
{
    use HasFactory;

    protected $table = 'tipo_opcional';
    protected $fillable = [
        'nome',
        'ativo',
    ];

    // Relacionamento com os opcionais
    public function opcionais()
    {
        return $this->hasMany(Opcional::class, 'tipo_opcional_id');
    }
}
