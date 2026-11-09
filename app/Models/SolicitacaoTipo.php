<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tipo de requerimento cadastrado pela secretaria (declaracao,
 * transferencia, atendimento...). Pode exigir anexo e definir
 * campos extras que a familia preenche no portal — as respostas
 * vao para solicitacoes.dados.
 */
class SolicitacaoTipo extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'solicitacao_tipos';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'nome',
        'descricao',
        'exige_anexo',
        'campos',
        'ativo',
    ];

    protected $casts = [
        'exige_anexo' => 'boolean',
        'ativo' => 'boolean',
        'campos' => 'array',
    ];

    /** Tipos de campo que a secretaria pode pedir no requerimento. */
    public const CAMPO_TIPOS = [
        'texto' => 'Texto curto',
        'texto_longo' => 'Texto longo',
        'numero' => 'Número',
        'data' => 'Data',
        'selecao' => 'Lista de opções',
        'anexo' => 'Anexo (arquivo)',
    ];

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function solicitacoes(): HasMany
    {
        return $this->hasMany(Solicitacao::class, 'tipo_solicitacao_id');
    }
}
