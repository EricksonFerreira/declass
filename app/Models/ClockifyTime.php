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
        'lider_id',
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

    public function lider()
    {
        return $this->belongsTo(User::class, 'lider_id');
    }

    /**
     * Admin pode gerenciar qualquer time; lider apenas o proprio time.
     */
    public function podeSerGerenciadoPor(User $user): bool
    {
        return app(\App\Services\VisibilidadeService::class)->podeGerenciarTime($user, $this);
    }
}
