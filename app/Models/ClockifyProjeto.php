<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClockifyProjeto extends Model
{
    use HasAuditColumns;

    protected $table = 'clockify_projetos';

    protected $fillable = [
        'empresa_id',
        'clockify_cliente_id',
        'nome',
        'descricao',
        'cor',
        'publico',
        'ativo',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'publico' => 'boolean',
        'ativo' => 'boolean',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(ClockifyCliente::class, 'clockify_cliente_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clockify_projeto_user', 'clockify_projeto_id', 'user_id')
            ->withTimestamps();
    }

    public function trackers(): HasMany
    {
        return $this->hasMany(ClockifyTimeTracker::class, 'clockify_projeto_id');
    }
}
