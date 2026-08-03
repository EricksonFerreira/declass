<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ChamadoAnexo extends Model
{
    use HasAuditColumns;

    protected $fillable = ['chamado_id', 'chamado_log_id', 'contexto', 'user_id', 'path', 'original_name', 'mime_type', 'size', 'usuinc', 'datainc', 'usuatu', 'datatu'];

    protected $casts = [
        'size' => 'integer',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function chamado()
    {
        return $this->belongsTo(Chamado::class, 'chamado_id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::url($this->path) : null;
    }
}
