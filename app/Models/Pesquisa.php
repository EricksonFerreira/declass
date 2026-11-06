<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pesquisa extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
        'status',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public const STATUS = [
        'rascunho' => 'Rascunho',
        'aberta' => 'Aberta',
        'encerrada' => 'Encerrada',
    ];

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function perguntas(): HasMany
    {
        return $this->hasMany(PesquisaPergunta::class, 'pesquisa_id')->orderBy('ordem');
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(PesquisaResposta::class, 'pesquisa_id');
    }

    public function statusLabel(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function estaAberta(): bool
    {
        return $this->status === 'aberta'
            && $this->data_inicio?->lte(now()->startOfDay())
            && $this->data_fim?->gte(now()->startOfDay());
    }
}
