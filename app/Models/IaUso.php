<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log de cada chamada de IA: empresa, usuário, provedor/modelo, recurso
 * de origem, tokens consumidos, custo estimado e status. Alimenta o
 * monitoramento de uso por empresa (superadmin) e o resumo na tela de
 * configuração da empresa.
 */
class IaUso extends Model
{
    protected $table = 'ia_usos';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'provedor',
        'modelo',
        'recurso',
        'tokens_entrada',
        'tokens_saida',
        'custo_estimado',
        'status',
        'erro',
        'duracao_ms',
    ];

    protected $casts = [
        'tokens_entrada' => 'integer',
        'tokens_saida' => 'integer',
        'custo_estimado' => 'float',
        'duracao_ms' => 'integer',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
