<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Post do forum da disciplina dentro da turma. parent_id nulo =
 * topico; com parent_id = comentario/resposta de um topico.
 */
class TurmaForumPost extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'turma_forum_posts';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'turma_id',
        'disciplina_id',
        'user_id',
        'parent_id',
        'titulo',
        'mensagem',
        'encerrada_em',
        'encerrada_por',
    ];

    protected $casts = [
        'encerrada_em' => 'datetime',
    ];

    /** Topico encerrado: visivel, mas nao aceita novas respostas. */
    public function estaEncerrada(): bool
    {
        return $this->encerrada_em !== null;
    }

    /** Atalho de leitura em Blade: $model->esta_encerrada */
    public function getEstaEncerradaAttribute()
    {
        return $this->estaEncerrada();
    }

    /**
     * Mensagem escapada para exibicao, com marcadores estilo WhatsApp:
     * *negrito* vira <strong> e _italico_ vira <em>. O italico exige
     * borda fora de palavra para nao quebrar identificadores snake_case.
     */
    public function getMensagemFormatadaAttribute(): string
    {
        $texto = e($this->mensagem);
        $texto = preg_replace('/\*(\S[^*\n]*?)\*/', '<strong>$1</strong>', $texto);
        $texto = preg_replace('/(?<![\w_])_(\S[^_\n]*?)_(?![\w_])/', '<em>$1</em>', $texto);

        return nl2br($texto);
    }

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

    public function respostas(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('id');
    }

    public function topico(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
