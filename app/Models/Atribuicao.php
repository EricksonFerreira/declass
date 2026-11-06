<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Atribuicao de professor a turma (e opcionalmente a uma
 * disciplina ofertada). `user_id` aponta para o usuario com
 * perfil de professor.
 */
class Atribuicao extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'atribuicoes';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'turma_id',
        'disciplina_id',
        'user_id',
        'papel',
        'ativo',
    ];

    public const PAPEIS = [
        'titular' => 'Titular',
        'auxiliar' => 'Auxiliar',
        'substituto' => 'Substituto',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estaAtiva(): bool
    {
        return strtoupper((string) $this->ativo) === 'S';
    }
}
