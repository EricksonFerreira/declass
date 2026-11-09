<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Mensagem da conversa de uma solicitacao: escrita pela familia
 * (user_id = autor da solicitacao) ou pela secretaria, ate o
 * atendimento final. Pode levar anexos (ex.: documento pedido).
 */
class SolicitacaoMensagem extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'solicitacao_mensagens';

    protected $fillable = [
        'empresa_id',
        'solicitacao_id',
        'user_id',
        'mensagem',
    ];

    public function solicitacao(): BelongsTo
    {
        return $this->belongsTo(Solicitacao::class, 'solicitacao_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Anexos enviados junto desta mensagem (documento pedido, comprovante...). */
    public function anexos(): HasMany
    {
        return $this->hasMany(SolicitacaoAnexo::class, 'mensagem_id');
    }

    /** Mensagem veio da familia (autor da solicitacao) ou da secretaria. */
    public function daFamilia(): bool
    {
        return $this->user_id === $this->solicitacao?->user_id;
    }
}
