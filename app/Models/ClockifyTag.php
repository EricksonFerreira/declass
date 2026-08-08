<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClockifyTag extends Model
{
    use HasAuditColumns;

    protected $table = 'clockify_tags';

    protected $fillable = [
        'empresa_id',
        'nome',
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

    public function trackers(): BelongsToMany
    {
        return $this->belongsToMany(ClockifyTimeTracker::class, 'clockify_tracker_tag', 'clockify_tag_id', 'clockify_time_tracker_id');
    }
}
