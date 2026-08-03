<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimentacao extends Model
{
    use HasFactory, HasAuditColumns;

    protected $table = 'movimentacoes';

    protected $fillable = [
        'nome',
        'ativo',
        'veiculo_id',
        'modelo_id',
        'tipo_movimentacao_id',
        'local_origem_id',
        'local_destino_id',
        'ordem_servico_id',
        'status_movimentacao_id',
        'reserva_id',
        'data_registro',
        'data_saida',
        'km_saida',
        'data_chegada_prevista',
        'data_chegada_real',
        'gera_disponibilidade',
        'km_chegada',
        'observacoes',
        'user_id',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'veiculo_id' => 'integer',
        'modelo_id' => 'integer',
        'tipo_movimentacao_id' => 'integer',
        'local_origem_id' => 'integer',
        'local_destino_id' => 'integer',
        'ordem_servico_id' => 'integer',
        'status_movimentacao_id' => 'integer',
        'reserva_id' => 'integer',
        'data_registro' => 'datetime',
        'data_saida' => 'datetime',
        'data_chegada_prevista' => 'datetime',
        'data_chegada_real' => 'datetime',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoMovimentacao()
    {
        return $this->belongsTo(TipoMovimentacao::class, 'tipo_movimentacao_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'modelo_id');
    }

    public function localOrigem()
    {
        return $this->belongsTo(Estacao::class, 'local_origem_id');
    }

    public function localDestino()
    {
        return $this->belongsTo(Estacao::class, 'local_destino_id');
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function statusMovimentacao()
    {
        return $this->belongsTo(StatusMovimentacao::class, 'status_movimentacao_id');
    }

    public function historico()
    {
        return $this->hasMany(MovimentacaoHistorico::class, 'movimentacao_id')
            ->orderByDesc('created_at');
    }
}
