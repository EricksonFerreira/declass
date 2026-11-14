<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Refeicao do cardapio de uma escola em uma data.
 */
class Cardapio extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    public const TIPOS = [
        'cafe_manha' => 'Café da manhã',
        'almoco' => 'Almoço',
        'lanche_tarde' => 'Lanche da tarde',
        'lanche_manha' => 'Lanche da manhã',
        'jantar' => 'Jantar',
    ];

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'data',
        'tipo',
        'descricao',
        'restricoes_alimentares',
        'observacoes',
        'ativo',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /** Atalho de leitura em Blade: $model->tipo_label */
    public function getTipoLabelAttribute()
    {
        return $this->tipoLabel();
    }

    public function estaAtivo(): bool
    {
        return $this->ativo === 'S';
    }

    /** Atalho de leitura em Blade: $model->esta_ativo */
    public function getEstaAtivoAttribute()
    {
        return $this->estaAtivo();
    }
}
