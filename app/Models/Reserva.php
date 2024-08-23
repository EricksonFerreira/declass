<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'user_id',
        'veiculo_id',
        'estacao_retirada_id',
        'estacao_devolucao_id',
        'data_inicio',
        'data_fim',
        'status',
        'data_criacao',
        'data_cancelamento',
    ];

    // Relacionamento com o modelo de Usuário
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relacionamento com o modelo de Veículo
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    // Relacionamento com a estação de retirada
    public function estacaoRetirada()
    {
        return $this->belongsTo(Estacao::class, 'estacao_retirada_id');
    }

    // Relacionamento com a estação de devolução
    public function estacaoDevolucao()
    {
        return $this->belongsTo(Estacao::class, 'estacao_devolucao_id');
    }

    // Relacionamento com os opcionais
    public function opcionais()
    {
        return $this->belongsToMany(Opcional::class, 'reservas_opcionais', 'reserva_id', 'opcional_id')
            ->withPivot('quantidade', 'preco_total');
    }
}
