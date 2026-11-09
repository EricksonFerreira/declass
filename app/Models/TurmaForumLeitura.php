<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Leitura de um topico do forum por usuario: lido_em e o instante
 * ate onde a pessoa viu a conversa (estilo WhatsApp).
 */
class TurmaForumLeitura extends Model
{
    protected $table = 'turma_forum_leituras';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'post_id',
        'user_id',
        'lido_em',
    ];

    protected $casts = [
        'lido_em' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(TurmaForumPost::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
