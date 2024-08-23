<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempoEntreEntacao extends Model
{
    use HasFactory;
    protected $table = 'tempo_entre_estacoes';

    protected $fillable = [
        'estacao_origem_id',
        'estacao_destino_id',
        'tempo_deslocamento',
    ];

    // Relacionamento com a estação de origem
    public function estacaoOrigem()
    {
        return $this->belongsTo(Estacao::class, 'estacao_origem_id');
    }

    // Relacionamento com a estação de destino
    public function estacaoDestino()
    {
        return $this->belongsTo(Estacao::class, 'estacao_destino_id');
    }
}
