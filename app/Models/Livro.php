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
        'localizacao',
        'capa_path',
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

    /** URL publica da capa cadastrada (null quando nao ha foto). */
    public function capaUrl(): ?string
    {
        return $this->capa_path ? asset('storage/'.$this->capa_path) : null;
    }

    /** Atalho de leitura em Blade: $model->capa_url */
    public function getCapaUrlAttribute()
    {
        return $this->capaUrl();
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

    /** Atalho de leitura em Blade: $model->disponiveis */
    public function getDisponiveisAttribute()
    {
        return $this->disponiveis();
    }

    public function temDisponiveis(): bool
    {
        return $this->disponiveis() > 0;
    }

    public function estaAtivo(): bool
    {
        return $this->ativo === 'S';
    }

    /** Atalho de leitura em Blade: $model->esta_ativo */
    public function getEstaAtivoAttribute()
    {
        return $this->estaAtivo();
    }
}
