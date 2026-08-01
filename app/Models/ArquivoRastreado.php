<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArquivoRastreado extends Model
{
    protected $table = 'arquivos_rastreados';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'modulo',
        'entidade',
        'entidade_id',
        'campo',
        'contexto',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'hash_sha1',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'size_bytes' => 'integer',
    ];
}
