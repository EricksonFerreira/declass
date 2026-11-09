<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Material de apoio postado pelo professor na turma: link externo
 * ou arquivo subido no storage (pdf, slides, video...).
 */
class TurmaMaterial extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'turma_materiais';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'turma_id',
        'disciplina_id',
        'user_id',
        'titulo',
        'descricao',
        'url',
        'arquivo_path',
    ];

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function arquivoUrl(): ?string
    {
        return $this->arquivo_path ? Storage::disk('public')->url($this->arquivo_path) : null;
    }

    public function tipo(): string
    {
        if ($this->arquivo_path) {
            return 'arquivo';
        }

        return $this->url ? 'link' : 'texto';
    }
}
