<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Documento que a escola solicita da familia/aluno
 * (RG, comprovante, atestado...). A familia envia o
 * arquivo pelo portal; a secretaria confere.
 */
class DocumentoSolicitacao extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'documento_solicitacoes';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'aluno_id',
        'user_id',
        'tipo',
        'titulo',
        'descricao',
        'prazo',
        'status',
        'arquivo_path',
        'enviado_em',
        'observacao',
    ];

    protected $casts = [
        'enviado_em' => 'datetime',
        'prazo' => 'date',
    ];

    public const STATUS = [
        'pendente' => 'Pendente',
        'enviado' => 'Aguardando conferência',
        'aceito' => 'Aceito',
        'devolvido' => 'Devolvido — reenviar',
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function statusLabel(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function arquivoUrl(): ?string
    {
        return $this->arquivo_path ? Storage::disk('public')->url($this->arquivo_path) : null;
    }

    public function tipoLabel(): string
    {
        return AlunoDocumento::TIPOS[$this->tipo] ?? $this->tipo;
    }
}
