<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ChamadoAnexo extends Model
{
    protected $fillable = ['chamado_id', 'chamado_log_id', 'contexto', 'user_id', 'path', 'original_name', 'mime_type', 'size'];

    public function chamado()
    {
        return $this->belongsTo(Chamado::class, 'chamado_id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::url($this->path) : null;
    }
}
