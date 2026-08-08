<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArquivoRastreado extends Model
{
    use HasFactory;

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
        'entidade_id' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
