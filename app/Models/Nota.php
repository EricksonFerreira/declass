<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Nota de um aluno em uma avaliacao. NULL = nao lancada.
 */
class Nota extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'avaliacao_id',
        'matricula_id',
        'nota',
        'observacao',
    ];

    protected $casts = [
        'nota' => 'float',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function avaliacao(): BelongsTo
    {
        return $this->belongsTo(Avaliacao::class, 'avaliacao_id');
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }
}
