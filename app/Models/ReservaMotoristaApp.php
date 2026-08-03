<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReservaMotoristaApp extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'reservas_motorista_app';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'motorista_app_solicitacao_id',
        'plano_motorista_app_id',
        'veiculo_id',
        'status',
        'status_pagamento',
        'pagamento_efetuado',
        'estacao_retirada_id',
        'data_inicio',
        'data_fim',
        'data_retirada_real',
        'contrato_efetivado_em',
        'checkin_realizado_em',
        'checkout_realizado_em',
        'quantidade_dias',
        'quilometragem_inicial',
        'nivel_combustivel_inicial',
        'quilometragem_final',
        'nivel_combustivel_final',
        'valor_plano',
        'valor_periodo_contrato',
        'valor_opcionais',
        'valor_caucao',
        'valor_total_final',
        'periodo_contrato',
        'contrato_snapshot_json',
        'contrato_pdf_path',
        'opcionais_json',
        'dados_json',
        'documentos_json',
        'observacoes',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'pagamento_efetuado' => 'boolean',
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'data_retirada_real' => 'datetime',
        'contrato_efetivado_em' => 'datetime',
        'checkin_realizado_em' => 'datetime',
        'checkout_realizado_em' => 'datetime',
        'quantidade_dias' => 'integer',
        'quilometragem_inicial' => 'integer',
        'quilometragem_final' => 'integer',
        'valor_plano' => 'decimal:2',
        'valor_periodo_contrato' => 'decimal:2',
        'valor_opcionais' => 'decimal:2',
        'valor_caucao' => 'decimal:2',
        'valor_total_final' => 'decimal:2',
        'contrato_snapshot_json' => 'array',
        'opcionais_json' => 'array',
        'dados_json' => 'array',
        'documentos_json' => 'array',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function solicitacao(): BelongsTo
    {
        return $this->belongsTo(MotoristaAppSolicitacao::class, 'motorista_app_solicitacao_id');
    }

    public function planoMotoristaApp(): BelongsTo
    {
        return $this->belongsTo(PlanoMotoristaApp::class, 'plano_motorista_app_id');
    }

    public function estacaoRetirada(): BelongsTo
    {
        return $this->belongsTo(Estacao::class, 'estacao_retirada_id');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function codigoReserva(): HasOne
    {
        return $this->hasOne(ReservaCodigo::class, 'reserva_motorista_app_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(ReservaMotoristaAppPagamento::class, 'reserva_motorista_app_id')
            ->orderByDesc('id');
    }

    public function multas(): HasMany
    {
        return $this->hasMany(Multa::class, 'reserva_motorista_app_id')->orderByDesc('id');
    }

    public function sinistros(): HasMany
    {
        return $this->hasMany(Sinistro::class, 'reserva_motorista_app_id')->orderByDesc('id');
    }

    // Aditivos de renovação (Termos Aditivos)
    public function aditivos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReservaMotoristaAppAditivo::class, 'reserva_motorista_app_id');
    }
}
