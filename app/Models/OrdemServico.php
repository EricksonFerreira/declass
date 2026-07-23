<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdemServico extends Model
{
    protected $table = 'ordens_servico';

    public const STATUS_ABERTA = 'aberta';
    public const STATUS_EM_ANDAMENTO = 'em_andamento';
    public const STATUS_CONCLUIDA = 'concluida';
    public const STATUS_CANCELADA = 'cancelada';

    protected $fillable = [
        'numero',
        'empresa_id',
        'veiculo_id',
        'reserva_id',
        'sinistro_id',
        'os_categoria_id',
        'os_subcategoria_id',
        'user_id_abertura',
        'user_id_encerramento',
        'status',
        'observacoes',
        'relatorio_conclusao',
        'valor_pecas',
        'valor_mao_obra',
        'valor_total',
        'data_conclusao',
    ];

    protected $casts = [
        'data_conclusao' => 'datetime',
        'valor_pecas' => 'decimal:2',
        'valor_mao_obra' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function sinistro(): BelongsTo
    {
        return $this->belongsTo(Sinistro::class, 'sinistro_id');
    }

    public function usuarioAbertura(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id_abertura');
    }

    public function usuarioEncerramento(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id_encerramento');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(OrdemServicoItem::class, 'ordem_servico_id')->orderBy('sort_order')->orderBy('id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(OsCategoria::class, 'os_categoria_id');
    }

    public function subcategoria(): BelongsTo
    {
        return $this->belongsTo(OsSubcategoria::class, 'os_subcategoria_id');
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(OrdemServicoAnexo::class, 'ordem_servico_id')->orderByDesc('id');
    }

    public function valoresCampos(): HasMany
    {
        return $this->hasMany(OrdemServicoCampoValor::class, 'ordem_servico_id');
    }

    public function scopeAbertasOuEmAndamento($query)
    {
        return $query->whereIn('status', [self::STATUS_ABERTA, self::STATUS_EM_ANDAMENTO]);
    }

    public function podeAlterarStatus(): bool
    {
        return in_array($this->status, [self::STATUS_ABERTA, self::STATUS_EM_ANDAMENTO], true);
    }

    public function podeConcluir(): bool
    {
        return in_array($this->status, [self::STATUS_ABERTA, self::STATUS_EM_ANDAMENTO], true);
    }
}
