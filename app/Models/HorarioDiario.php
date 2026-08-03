<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioDiario extends Model
{
    use HasFactory;

    protected $table = 'horarios_diarios';
    protected $fillable = [
        'nome',
        'ativo',
        'empresa_id',
    ];

    public function estacoes()
    {
        return $this->belongsToMany(Estacao::class, 'horarios', 'horario_diario_id', 'estacao_id');
    }

    public function getFiliaisLabelAttribute(): string
    {
        if ($this->relationLoaded('estacoes') && $this->estacoes->isNotEmpty()) {
            return $this->estacoes->pluck('nome')->implode(', ');
        }

        return 'Sem filial';
    }

}
