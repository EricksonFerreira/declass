<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClockifyTimeOff extends Model
{
    use HasAuditColumns;

    protected $table = 'clockify_time_offs';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'tipo',
        'data_inicio',
        'data_fim',
        'status',
        'observacao',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes();
    }
}
