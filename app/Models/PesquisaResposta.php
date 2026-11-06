<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesquisaResposta extends Model
{
    use HasAuditColumns;

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'pesquisa_id',
        'user_id',
        'aluno_id',
        'respostas',
    ];

    protected $casts = [
        'respostas' => 'array',
    ];

    public function pesquisa(): BelongsTo
    {
        return $this->belongsTo(Pesquisa::class, 'pesquisa_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }
}
