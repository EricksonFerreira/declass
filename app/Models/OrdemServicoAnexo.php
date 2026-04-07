<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdemServicoAnexo extends Model
{
    protected $table = 'ordem_servico_anexos';

    protected $fillable = [
        'ordem_servico_id',
        'path',
        'original_name',
        'mime',
        'size_bytes',
        'user_id',
    ];

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

