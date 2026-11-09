<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Arquivo anexado pela familia a uma solicitacao
 * (exigido ou opcional conforme o tipo de requerimento).
 */
class SolicitacaoAnexo extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'solicitacao_anexos';

    protected $fillable = [
        'empresa_id',
        'solicitacao_id',
        'mensagem_id',
        'arquivo_path',
        'nome_original',
    ];

    public function solicitacao(): BelongsTo
    {
        return $this->belongsTo(Solicitacao::class, 'solicitacao_id');
    }

    /** Mensagem da conversa a que este anexo pertence (nulo = anexo da abertura). */
    public function mensagem(): BelongsTo
    {
        return $this->belongsTo(SolicitacaoMensagem::class, 'mensagem_id');
    }

    public function nomeExibicao(): string
    {
        return $this->nome_original ?: basename((string) $this->arquivo_path);
    }
}
