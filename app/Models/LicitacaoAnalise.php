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
        'preco_sugerido',
        'tokens_entrada',
        'tokens_saida',
    ];

    protected $casts = [
        'nota_tecnica_estimada' => 'decimal:2',
        'preco_sugerido' => 'decimal:2',
        'criterios' => 'array',
        'exigencias' => 'array',
        'gaps' => 'array',
    ];

    public const VIABILIDADES = [
        'viavel' => 'Viável',
        'atencao' => 'Viável com ressalvas',
        'inviavel' => 'Inviável',
    ];

    public function licitacao(): BelongsTo
    {
        return $this->belongsTo(Licitacao::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
