<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Aviso/comunicado publicado pela escola para as familias.
 * turma_id nulo significa aviso geral da escola.
 */
class Aviso extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'turma_id',
        'titulo',
        'mensagem',
        'tipo',
        'destino',
        'data_publicacao',
        'ativo',
    ];

    protected $casts = [
        'data_publicacao' => 'date',
    ];

    public const TIPOS = [
        'info' => 'Informativo',
        'urgente' => 'Urgente',
        'evento' => 'Evento',
    ];

    /** Publico do aviso: familias (portal) ou equipe (mural interno). */
    public const DESTINOS = [
        'familias' => 'Famílias (portal)',
        'staff' => 'Equipe (mural interno)',
    ];

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
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

    public function turmas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Turma::class, 'aviso_turma');
    }

    public function ehGeral(): bool
    {
        return $this->turma_id === null && $this->turmas->isEmpty();
    }

    /** Atalho de leitura em Blade: $model->eh_geral */
    public function getEhGeralAttribute()
    {
        return $this->ehGeral();
    }

    /** Nome exibido na coluna Publico. */
    public function publicoLabel(): string
    {
        if ($this->turma_id !== null) {
            return $this->turma->nome ?? 'Turma #'.$this->turma_id;
        }
        $nomes = $this->turmas->pluck('nome');

        return $nomes->isEmpty() ? 'Escola toda' : $nomes->take(3)->implode(', ')
            .($nomes->count() > 3 ? ' +'.($nomes->count() - 3) : '');
    }

    /** Atalho de leitura em Blade: $model->publico_label */
    public function getPublicoLabelAttribute()
    {
        return $this->publicoLabel();
    }

    /**
     * Comunicados visiveis no site publico: publicados para familias
     * e ja no ar. Avisos internos (staff) nunca entram.
     */
    public function scopePublicosSite($query)
    {
        return $query
            ->where('destino', 'familias')
            ->where('ativo', 'S')
            ->whereDate('data_publicacao', '<=', today())
            ->orderByDesc('data_publicacao')
            ->orderByDesc('id');
    }
}
