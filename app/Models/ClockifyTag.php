<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClockifyTag extends Model
{
    use HasAuditColumns;

    protected $table = 'clockify_tags';

    protected $fillable = [
        'empresa_id',
        'clockify_projeto_id',
        'nome',
        'cor',
        'ativo',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    /**
     * Tag vinculada a um projeto (modo "tags por projeto").
     * null = tag global, disponivel em qualquer projeto.
     */
    public function projeto(): BelongsTo
    {
        return $this->belongsTo(ClockifyProjeto::class, 'clockify_projeto_id');
    }

    public function trackers(): BelongsToMany
    {
        return $this->belongsToMany(ClockifyTimeTracker::class, 'clockify_tracker_tag', 'clockify_tag_id', 'clockify_time_tracker_id');
    }

    /**
     * Cor da tag em hex; cai num azul padrao quando nao definida.
     */
    public function getCorCssAttribute(): string
    {
        $cor = trim((string) $this->cor);

        return preg_match('/^#[0-9a-fA-F]{6}$/', $cor) ? $cor : '#2563eb';
    }
}
