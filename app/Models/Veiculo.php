<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        'valor_compra',
        'valor_fipe',
        'codigo_fipe',
        'referencia_fipe',
        'data_consulta_fipe',
        'taxa_depreciacao_anual',
    ];

    protected $casts = [
        'valor_compra' => 'decimal:2',
        'valor_fipe' => 'decimal:2',
        'data_consulta_fipe' => 'date',
        'taxa_depreciacao_anual' => 'decimal:2',
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

    public function multas()
    {
        return $this->hasMany(\App\Models\Multa::class, 'veiculo_id');
    }

    public function sinistros()
    {
        return $this->hasMany(\App\Models\Sinistro::class, 'veiculo_id');
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
            ->whereIn('status_movimentacao_id', $this->getStatusMovimentacaoIndisponiveisIds()) // Status por trecho de nome
            ->first();

        return $movimentacao == null;
    }

    private function getStatusMovimentacaoIndisponiveisIds(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        if (!Schema::hasTable('status_movimentacao')) {
            return $cache = [];
        }

        try {
            $ids = DB::table('status_movimentacao')
                ->where(function ($query) {
                    $query->whereRaw('LOWER(nome) LIKE ?', ['%conclu%'])
                        ->orWhereRaw('LOWER(nome) LIKE ?', ['%indispon%'])
                        ->orWhereRaw('LOWER(nome) LIKE ?', ['%transit%'])
                        ->orWhereRaw('LOWER(nome) LIKE ?', ['%manuten%'])
                        ->orWhereRaw('LOWER(nome) LIKE ?', ['%espera%']);
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            return $cache = $ids;
        } catch (\Throwable $e) {
            return $cache = [];
        }
    }

    public function getDisponibilidade($dia)
    {
        return $this->reservas->where('data_reserva', $dia)->first();
    }

    public function getDiferencaFipeCompraAttribute(): ?float
    {
        if ($this->valor_compra === null && $this->valor_fipe === null) {
            return null;
        }

        return round(((float) ($this->valor_fipe ?? 0) - (float) ($this->valor_compra ?? 0)), 2);
    }

    public function getPercentualDepreciacaoAcumuladaAttribute(): ?float
    {
        if (empty($this->data_compra) || $this->taxa_depreciacao_anual === null) {
            return null;
        }

        $dataCompra = Carbon::parse($this->data_compra);
        $mesesEmUso = $dataCompra->diffInMonths(now());
        $percentual = ($mesesEmUso / 12) * (float) $this->taxa_depreciacao_anual;

        return round(min(100, max(0, $percentual)), 2);
    }

    public function getValorDepreciacaoAcumuladaAttribute(): ?float
    {
        $base = $this->valor_fipe ?? $this->valor_compra;
        $percentual = $this->percentual_depreciacao_acumulada;

        if ($base === null || $percentual === null) {
            return null;
        }

        return round(((float) $base * (float) $percentual) / 100, 2);
    }

    public function getValorAtualEstimadoAttribute(): ?float
    {
        $base = $this->valor_fipe ?? $this->valor_compra;
        if ($base === null) {
            return null;
        }

        $valorDepreciado = $this->valor_depreciacao_acumulada ?? 0;

        return round(max(0, (float) $base - (float) $valorDepreciado), 2);
    }

    public function getTemProtecaoDepreciacaoAttribute(): bool
    {
        if ($this->valor_compra === null || $this->valor_fipe === null) {
            return false;
        }

        return (float) $this->valor_compra < (float) $this->valor_fipe;
    }

    public function getValorProtegidoDepreciacaoAttribute(): ?float
    {
        if (!$this->tem_protecao_depreciacao) {
            return null;
        }

        return round((float) $this->valor_compra, 2);
    }

}
