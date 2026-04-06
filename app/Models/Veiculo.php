<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Veiculo extends Model
{
    use HasFactory;

    protected $table = 'veiculos';
    protected $fillable = [
        'nome',
        'ativo',
        'tipo_frota_id', // van, carro
        'grupo_frota_id', // frota
        'marca_id', // marca
        'modelo_id', // modelo
        'segmento_id', // segmento
        'status_veiculo_id', // status
        'ano_fabricacao',
        'ano_modelo',
        'placa_licenca',
        'chassi',
        'data_disponibilidade',
        // 'renavam',
        'cor',
        'data_placa_licenca',
        'data_compra',
        'data_validade',
        'kms',
        'limite_kms',
        'numero_motor',
        'vendido',
        'data_vendido',
        'proxima_revisao_kms',
        'proxima_manutencao_kms',
        'data_proxima_manutencao',
        'data_proxima_inspecao',
        'local_disponibilidade',
        'data_hora_disponibilidade',
    ];

    public function movimentacoes()
    {
        return $this->hasMany(Movimentacao::class, 'veiculo_id');
    }

    public function tipoFrota()
    {
        return $this->belongsTo(TipoFrota::class, 'tipo_frota_id');
    }

    public function grupoFrota()
    {
        return $this->belongsTo(GrupoFrota::class, 'grupo_frota_id');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'modelo_id');
    }

    public function segmento()
    {
        return $this->belongsTo(Segmento::class, 'segmento_id');
    }

    public function statusVeiculo()
    {
        return $this->belongsTo(StatusVeiculo::class, 'status_veiculo_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'veiculo_id');
    }

    public function veiculoDanos()
    {
        return $this->hasMany(\App\Models\VeiculoDanos::class, 'veiculo_id');
    }

    public function ordensServico()
    {
        return $this->hasMany(\App\Models\OrdemServico::class, 'veiculo_id');
    }

    public function localDisponibilidade()
    {
        return $this->belongsTo(Estacao::class, 'local_disponibilidade');
    }

    public function estaDisponivel($dia)
    {
        // Verificar se o veículo está disponível no dia especificado
        $movimentacao = DB::table("movimentacoes")
            ->where('veiculo_id', $this->id)
            // ->where('data_saida', '<=', $dia)
            ->where(function ($query) use ($dia) {
                $query->where('data_chegada_real', '>=', $dia)
                      ->orWhereNull('data_chegada_real');
            })
            ->whereIn('status_movimentacao_id', [1, 2, 5, 6, 7]) // IDs dos status que indicam indisponibilidade
            ->first();

        return $movimentacao == null;
    }

    public function getDisponibilidade($dia)
    {
        return $this->reservas->where('data_reserva', $dia)->first();
    }

}
