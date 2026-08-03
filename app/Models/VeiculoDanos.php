<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VeiculoDanos extends Model
{
    use HasAuditColumns, HasFactory;

    const STATUS_PENDENTE = 'pendente';
    const STATUS_EM_CORRECAO = 'em_correcao';
    const STATUS_CORRIGIDA = 'corrigida';
    const STATUS_PARCIALMENTE_CORRIGIDA = 'parcialmente_corrigida';
    const STATUS_DESCARTADA = 'descartada';

    const REVISAO_PENDENTE = 'pendente';
    const REVISAO_COBRAR_CLIENTE = 'cobrar_cliente';
    const REVISAO_NAO_COBRAR = 'nao_cobrar';
    const REVISAO_JA_EXISTENTE = 'ja_existente';
    const REVISAO_DESGASTE_NORMAL = 'desgaste_normal';
    const REVISAO_POSSIVEL_SINISTRO = 'possivel_sinistro';

    const REPARO_PENDENTE = 'pendente';
    const REPARO_EM_OS = 'em_os';
    const REPARO_EM_CORRECAO = 'em_correcao';
    const REPARO_CORRIGIDA = 'corrigida';
    const REPARO_NAO_REQUER_REPARO = 'nao_requer_reparo';

    protected $table = 'veiculo_danos';

    protected $fillable = [
        'categoria_id',
        'localizacao_problema',
        'dano_id',
        'tipo_dano',
        'local_dano',
        'custo_estimado',
        'quilometragem_veiculo',
        'observacoes',
        'veiculo_id',
        'reserva_id',
        'reserva_codigo_id',
        'contrato_id',
        'correcao_descricao',
        'correcao_data',
        'correcao_user_id',
        'status',
        'status_revisao',
        'status_reparo',
        'decisao_revisada_em',
        'decisao_revisada_por',
        'justificativa_decisao',
        'nao_requer_reparo_em',
        'nao_requer_reparo_por',
        'nao_requer_reparo_justificativa',
        'ordem_servico_id',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'correcao_data' => 'datetime',
        'decisao_revisada_em' => 'datetime',
        'nao_requer_reparo_em' => 'datetime',
        'custo_estimado' => 'decimal:2',
        'quilometragem_veiculo' => 'integer',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function dano(): BelongsTo
    {
        return $this->belongsTo(Dano::class, 'dano_id');
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function reservaCodigo(): BelongsTo
    {
        return $this->belongsTo(
            ReservaCodigo::class,
            'reserva_codigo_id'
        );
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(
            Reserva::class,
            'reserva_id'
        );
    }

    public function contratoCheckin(): BelongsTo
    {
        return $this->belongsTo(
            ContratoCheckin::class,
            'contrato_id'
        );
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(
            CategoriaDano::class,
            'categoria_id'
        );
    }

    public function ordemServicoItens(): HasMany
    {
        return $this->hasMany(
            OrdemServicoItem::class,
            'veiculo_dano_id'
        );
    }

    public function imagens(): HasMany
    {
        return $this->hasMany(
            VeiculoDanosImagem::class,
            'dano_veiculo_id'
        );
    }

    public function imagem(): HasMany
    {
        return $this->imagens();
    }

    public function usuarioCorrecao(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'correcao_user_id'
        );
    }

    public function usuarioDecisaoRevisao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decisao_revisada_por');
    }

    public function usuarioNaoRequerReparo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nao_requer_reparo_por');
    }

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function scopeSemCorrecao($query)
    {
        return $query->where('status_reparo', '!=', self::REPARO_CORRIGIDA)
            ->where('status_reparo', '!=', self::REPARO_NAO_REQUER_REPARO);
    }

    public function scopeComCorrecao($query)
    {
        return $query->where('status_reparo', self::REPARO_CORRIGIDA);
    }
}
