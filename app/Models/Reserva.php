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
        'quantidade_dias',
        'grupo_frota_id',
        'tarifa_base',
        'valor_total_tarifa_base',
        'percentual_desconto',
        'valor_total_com_desconto',
        'valor_opcionais',
        'valor_total_final',
    ];

    public function podeEditar()
    {
        return in_array($this->statusReserva->id, [1]);
    }

    public function podeEfetuarPagamento()
    {
        return in_array($this->statusReserva->id, [1]);
    }

    public function podeCriarContrato()
    {
        return in_array($this->statusReserva->id, [2]) && $this->contratoCheckin()->doesntExist();
    }

    public function podeAssinarContrato()
    {
        return in_array($this->statusReserva->id, [2]) && $this->contratoCheckin()->exists();
    }
    
    public function podeVerContrato()
    {
        return  $this->contratoCheckin()->exists();
    }

    public function podeFazerCancelamento()
    {
        return in_array($this->statusReserva->id, [1, 2]);
    }

    public function podeFazerCheckout()
    {
        return in_array($this->statusReserva->id, [4]);
    }
    

    // Relacionamentos
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function modelos()
    {
        return $this->belongsTo(Modelo::class, 'modelo_id');
    }
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function estacaoRetirada()
    {
        return $this->belongsTo(Estacao::class, 'estacao_retirada_id');
    }

    public function estacaoDevolucao()
    {
        return $this->belongsTo(Estacao::class, 'estacao_devolucao_id');
    }

    public function opcionais()
    {
        return $this->belongsToMany(Opcional::class, 'reservas_opcionais', 'reserva_id', 'opcional_id')
            ->withPivot('quantidade', 'preco_total');
    }

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

    public function contratoCheckin()
    {
        return $this->hasOne(ContratoCheckin::class, 'reserva_id');
    }

    public function contratoCheckout()
    {
        return $this->hasOne(ContratoCheckout::class, 'reserva_id');
    }

    public function veiculoDano(){
        return $this->hasMany(VeiculoDanos::class, 'reserva_id');
    }

    public function nfse()
    {
        return $this->hasOne(Nfse::class, 'reserva_id');
    }

    public function nfseAtual()
    {
        return $this->hasOne(Nfse::class, 'reserva_id')->latestOfMany();
    }

    public function transacoes()
    {
        return $this->hasMany(Transacao::class, 'reserva_id');
    }

    public function multas()
    {
        return $this->hasMany(Multa::class, 'reserva_id');
    }

    public function calcao()
    {
        return $this->hasOne(Calcao::class, 'reserva_id')->latest();
    }
}
