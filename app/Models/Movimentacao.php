<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model
{    use HasFactory;

    protected $table = 'movimentacoes';

    protected $fillable = [
        'veiculo_id',
        'modelo_id',
        'tipo_movimentacao_id',
        'local_origem_id',
        'local_destino_id',
        'ordem_servico_id',
        'status_movimentacao_id',
        'reserva_id',
        'data_registro',
        'observacoes',
        'user_id',
    ];

    // Relacionamento com User
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relacionamento com TipoMovimentacao
    public function tipoMovimentacao()
    {
        return $this->belongsTo(TipoMovimentacao::class, 'tipo_movimentacao_id');
    }

    // Relacionamento com Veiculo (opcional)
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    // Relacionamento com GrupoVeiculo
    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'modelo_id');
    }

    // Relacionamento com Local de Origem
    public function localOrigem()
    {
        return $this->belongsTo(Estacao::class, 'local_origem');
    }

    // Relacionamento com Local de Destino
    public function localDestino()
    {
        return $this->belongsTo(Estacao::class, 'local_destino');
    }

    // Relacionamento com Reserva (opcional)
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    // Relacionamento com Ordem de Serviço (opcional)
    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    // Relacionamento com StatusMovimentacao
    public function statusMovimentacao()
    {
        return $this->belongsTo(StatusMovimentacao::class, 'status_movimentacao_id');
    }
}

