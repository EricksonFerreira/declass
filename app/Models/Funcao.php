<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Funcao extends Model
{
    protected $table = 'funcoes';

    protected $fillable = [
        'empresa_id',
        'nome',
        'custo_hora',
        'ativo',
    ];

    protected $casts = [
        'custo_hora' => 'float',
        'ativo' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'funcao_id');
    }
}
