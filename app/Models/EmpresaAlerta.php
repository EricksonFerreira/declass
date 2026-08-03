<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaAlerta extends Model
{
    use HasAuditColumns;

    protected $table = 'empresa_alertas';

    protected $fillable = [
        'empresa_id',
        'tipo',
        'nivel',
        'titulo',
        'mensagem',
        'metadados',
        'resolvido',
        'resolvido_em',
        'resolvido_por_user_id',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'metadados'    => 'array',
        'resolvido'    => 'boolean',
        'resolvido_em' => 'datetime',
        'datainc'      => 'datetime',
        'datatu'       => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function resolvidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolvido_por_user_id');
    }

    public function getNivelClassAttribute(): string
    {
        return match ($this->nivel) {
            'critico' => 'danger',
            'aviso'   => 'warning',
            default   => 'info',
        };
    }

    public function getNivelIconeAttribute(): string
    {
        return match ($this->nivel) {
            'critico' => 'fas fa-times-circle',
            'aviso'   => 'fas fa-exclamation-triangle',
            default   => 'fas fa-info-circle',
        };
    }

    public function resolver(int $userId): void
    {
        $this->update([
            'resolvido'             => true,
            'resolvido_em'          => now(),
            'resolvido_por_user_id' => $userId,
        ]);
    }
}
