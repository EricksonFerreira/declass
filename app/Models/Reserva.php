<?php

namespace App\Models;

use App\Enums\StatusReservaEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'user_id',
        'criado_por_user_id',
        'origem_cadastro',
        'veiculo_id',
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
        'taxa_coleta',
        'taxa_entrega',
        'cobranca_efetuada_pessoalmente_em',
        'cobranca_efetuada_pessoalmente_por_user_id',
        'cobranca_efetuada_pessoalmente_justificativa',
        'revisao_checkout_concluida',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'data_criacao' => 'datetime',
        'data_cancelamento' => 'datetime',
        'pagamento_efetuado' => 'boolean',
        'status_reserva_id' => 'integer',
        'veiculo_id' => 'integer',
        'modelo_id' => 'integer',
        'estacao_retirada_id' => 'integer',
        'estacao_devolucao_id' => 'integer',
    ];

    public function podeEditar(): bool
    {
        return $this->status_reserva_id === StatusReservaEnum::Pendente->value;
    }

    public function podeEfetuarPagamento(): bool
    {
        return $this->status_reserva_id === StatusReservaEnum::Pendente->value;
    }

    public function isPagamentoEfetuado(): bool
    {
        return in_array($this->pagamento_efetuado, [true, 1, '1', 'S', 's', 'sim', 'SIM'], true);
    }

    public function podeCriarContrato(): bool
    {
        return $this->status_reserva_id === StatusReservaEnum::Confirmada->value
            && $this->contratoCheckin()->doesntExist();
    }

    public function podeAssinarContrato(): bool
    {
        return $this->status_reserva_id === StatusReservaEnum::Confirmada->value
            && $this->contratoCheckin()->exists();
    }

    public function podeVerContrato(): bool
    {
        return $this->contratoCheckin()->exists();
    }

    public function podeFazerCancelamento(): bool
    {
        if ($this->contratoCheckin()->exists()) {
            return false;
        }

        return in_array((int) $this->status_reserva_id, [
            StatusReservaEnum::Pendente->value,
            StatusReservaEnum::Confirmada->value,
        ], true);
    }

    public function podeCancelarComoCliente(): bool
    {
        if (! $this->podeFazerCancelamento()) {
            return false;
        }

        $baseCriacao = $this->created_at ?? $this->data_criacao ?? null;
        if (! $baseCriacao) {
            return false;
        }

        return Carbon::parse($baseCriacao)->addDay()->gt(now());
    }

    public function podeResponderSatisfacao(): bool
    {
        return $this->contratoCheckout()->exists() && ! $this->satisfacao()->exists();
    }

    public function podeFazerCheckout(): bool
    {
        return (int) $this->status_reserva_id === StatusReservaEnum::Checkin->value;
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por_user_id');
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

    public function statusReserva()
    {
        return $this->belongsTo(StatusReserva::class, 'status_reserva_id');
    }

    public function grupoFrota()
    {
        return $this->belongsTo(GrupoFrota::class, 'grupo_frota_id');
    }

    public function cupomDesconto()
    {
        return $this->belongsTo(CupomDesconto::class, 'cupom_desconto_id');
    }

    public function nfseAtual()
    {
        return $this->hasOne(Nfse::class, 'reserva_id')->latestOfMany('id');
    }

    public function satisfacao()
    {
        return $this->hasOne(ReservaSatisfacao::class, 'reserva_id');
    }

    public function calcao()
    {
        return $this->hasOne(Calcao::class, 'reserva_id');
    }

    public function veiculoDanos()
    {
        return $this->hasMany(VeiculoDanos::class, 'reserva_id')->orderByDesc('id');
    }

    public function avariaCobrancas(): HasManyThrough
    {
        return $this->hasManyThrough(
            AvariaCobranca::class,
            ReservaCodigo::class,

            'reserva_id',        // reserva_codigos.reserva_id
            'reserva_codigo_id', // avaria_cobrancas.reserva_codigo_id

            'id',                // reservas.id
            'id',                // reserva_codigos.id
        )->orderByDesc('avaria_cobrancas.id');
    }

    public function multas()
    {
        return $this->hasMany(Multa::class, 'reserva_id')->orderByDesc('id');
    }

    public function sinistros()
    {
        return $this->hasMany(Sinistro::class, 'reserva_id')->orderByDesc('id');
    }

    public function opcionais()
    {
        return $this->belongsToMany(Opcional::class, 'reservas_opcionais', 'reserva_id', 'opcional_id')
            ->withPivot('quantidade', 'preco_total')
            ->withTimestamps();
    }

    public function transacoes()
    {
        return $this->hasMany(Transacao::class, 'reserva_id')->orderByDesc('id');
    }

    public function contratoCheckin()
    {
        return $this->hasOne(ContratoCheckin::class, 'reserva_id');
    }

    public function contratoCheckout()
    {
        return $this->hasOne(ContratoCheckout::class, 'reserva_id');
    }

    public function codigoReserva()
    {
        return $this->hasOne(ReservaCodigo::class, 'reserva_id');
    }
}
