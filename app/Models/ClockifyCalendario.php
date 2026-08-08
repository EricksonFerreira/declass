<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClockifyCalendario extends Model
{
    use HasAuditColumns;

    protected $table = 'clockify_calendarios';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'clockify_projeto_id',
        'data_inicio',
        'data_fim',
        'horas_por_dia',
        'observacao',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'horas_por_dia' => 'decimal:2',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes();
    }

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(ClockifyProjeto::class, 'clockify_projeto_id');
    }
}
