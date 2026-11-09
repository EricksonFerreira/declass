<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Foto/arquivo enviado pelo aluno como parte da entrega
 * de uma atividade (modo de entrega "foto").
 */
class EntregaTarefaAnexo extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'entrega_tarefa_anexos';

    protected $fillable = [
        'empresa_id',
        'entrega_id',
        'arquivo_path',
        'nome_original',
    ];

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(EntregaTarefa::class, 'entrega_id');
    }

    public function url(): string
    {
        return asset('storage/'.$this->arquivo_path);
    }
}
