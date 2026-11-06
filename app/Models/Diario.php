<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Registro de uma aula no diario de classe.
 * Unico por turma + disciplina + data.
 */
class Diario extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'turma_id',
        'disciplina_id',
        'user_id',
        'data',
        'conteudo',
        'observacoes',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function frequencias(): HasMany
    {
        return $this->hasMany(Frequencia::class, 'diario_id');
    }
}
