<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Livro do acervo da biblioteca de uma escola.
 * A quantidade disponivel e derivada: exemplares menos
 * emprestimos com status 'ativo'.
 */
class Livro extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'titulo',
        'autor',
        'editora',
        'isbn',
        'categoria',
        'ano_publicacao',
        'exemplares',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ano_publicacao' => 'integer',
        'exemplares' => 'integer',
    ];

    public const CATEGORIAS = [
        'didatico' => 'Didático',
        'literatura' => 'Literatura',
        'infantil' => 'Infantil',
        'juvenil' => 'Juvenil',
        'referencia' => 'Referência',
        'religiao' => 'Religião',
        'outro' => 'Outro',
    ];

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function emprestimos(): HasMany
    {
        return $this->hasMany(EmprestimoLivro::class, 'livro_id');
    }

    public function emprestimosAtivos(): HasMany
    {
        return $this->emprestimos()->where('status', 'ativo');
    }

    public function emprestadosAtivos(): int
    {
        return $this->emprestimosAtivos()->count();
    }

    public function disponiveis(): int
    {
        return max(0, $this->exemplares - $this->emprestadosAtivos());
    }

    public function temDisponiveis(): bool
    {
        return $this->disponiveis() > 0;
    }

    public function estaAtivo(): bool
    {
        return $this->ativo === 'S';
    }
}
