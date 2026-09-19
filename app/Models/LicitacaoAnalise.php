<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicitacaoAnalise extends Model
{
    protected $table = 'licitacao_analises';

    protected $fillable = [
        'empresa_id',
        'licitacao_id',
        'user_id',
        'modelo',
        'nota_tecnica_estimada',
        'viabilidade',
        'parecer',
        'criterios',
        'exigencias',
        'gaps',
        'divergencia',
        'preco_sugerido',
        'preco_max_edital',
        'preco_motivo',
        'tokens_entrada',
        'tokens_saida',
    ];

    protected $casts = [
        'nota_tecnica_estimada' => 'decimal:2',
        'preco_sugerido' => 'decimal:2',
        'preco_max_edital' => 'decimal:2',
        'criterios' => 'array',
        'exigencias' => 'array',
        'gaps' => 'array',
        'divergencia' => 'array',
    ];

    public const VIABILIDADES = [
        'viavel' => 'Viável',
        'atencao' => 'Habilitação não confirmada',
        'inviavel' => 'Inviável',
    ];

    /** Índice interno de aderência (0-100) — não é pontuação do edital. */
    public function getIndiceAderenciaAttribute(): ?float
    {
        return $this->nota_tecnica_estimada !== null ? (float) $this->nota_tecnica_estimada : null;
    }

    public function licitacao(): BelongsTo
    {
        return $this->belongsTo(Licitacao::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
