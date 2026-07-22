<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VeiculoDanos extends Model
{
    use HasFactory;

    const STATUS_PENDENTE = 'pendente';
    const STATUS_EM_CORRECAO = 'em_correcao';
    const STATUS_CORRIGIDA = 'corrigida';
    const STATUS_PARCIALMENTE_CORRIGIDA = 'parcialmente_corrigida';
    const STATUS_DESCARTADA = 'descartada';

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
    ];

    protected $casts = [
        'correcao_data' => 'datetime',
        'custo_estimado' => 'decimal:2',
        'quilometragem_veiculo' => 'integer',
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

    public function scopeSemCorrecao($query)
    {
        return $query->whereIn('status', ['pendente', 'em_correcao']);
    }

    public function scopeComCorrecao($query)
    {
        return $query->whereIn('status', ['corrigida', 'parcialmente_corrigida', 'descartada']);
    }
}
