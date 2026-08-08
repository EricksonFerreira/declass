<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClockifyTime extends Model
{
    use HasAuditColumns;

    protected $table = 'clockify_times';

    protected $fillable = [
        'empresa_id',
        'nome',
        'descricao',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clockify_time_user', 'clockify_time_id', 'user_id')
            ->withTimestamps();
    }
}
