<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClockifyTimeTracker extends Model
{
    use HasAuditColumns;

    protected $table = 'clockify_time_trackers';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'clockify_projeto_id',
        'descricao',
        'inicio',
        'fim',
        'faturavel',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fim' => 'datetime',
        'faturavel' => 'boolean',
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

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ClockifyTag::class, 'clockify_tracker_tag', 'clockify_time_tracker_id', 'clockify_tag_id')
            ->withTimestamps();
    }

    public function getDurationInSecondsAttribute(): int
    {
        if (!$this->inicio) {
            return 0;
        }

        $end = $this->fim ?: now();
        return $this->inicio->diffInSeconds($end);
    }

    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->duration_in_seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
