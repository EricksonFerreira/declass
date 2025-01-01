<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'user_id',
        'modelo_id',
        'estacao_retirada_id',
        'estacao_devolucao_id',
        'data_inicio',
        'data_fim',
        'status_reserva_id',
        'data_criacao',
        'data_cancelamento',
        'pagamento_efetuado',
        'cupom_desconto_id',
        'observacoes',
        'tarifa_base',
        'quantidade_dias',
        'grupo_frota_id',
        'valor_desconto',
        'valor_total',
        'valor_opcionais',
        'valor_total_por_dias',
    ];

    // Relacionamento com o modelo de Usuário
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relacionamento com o modelo de Modelo
    public function modelos()
    {
        return $this->belongsTo(Modelo::class, 'modelo_id');
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

    // Relacionamento com o status
    public function statusReserva()
    {
        return $this->belongsTo(StatusReserva::class, 'status_reserva_id');
    }

    public function cupomDesconto()
    {
        return $this->belongsTo(CupomDesconto::class, 'cupom_desconto_id');
    }

    public function grupoFrota()
    {
        return $this->belongsTo(GrupoFrota::class, 'grupo_frota_id');
    }
}
