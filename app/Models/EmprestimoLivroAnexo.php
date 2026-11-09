<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Foto registrada na devolucao de um livro (avarias: rasgos,
 * capa danificada, paginas faltando etc.).
 */
class EmprestimoLivroAnexo extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'emprestimo_livro_anexos';

    protected $fillable = [
        'empresa_id',
        'emprestimo_id',
        'arquivo_path',
        'nome_original',
    ];

    public function emprestimo(): BelongsTo
    {
        return $this->belongsTo(EmprestimoLivro::class, 'emprestimo_id');
    }

    public function url(): string
    {
        return asset('storage/'.$this->arquivo_path);
    }
}
