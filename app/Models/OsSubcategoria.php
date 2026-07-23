<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsSubcategoria extends Model
{
    protected $table = 'os_subcategorias';

    protected $fillable = [
        'os_categoria_id',
        'nome',
        'show_sinistro',
        'show_reserva',
        'show_avarias',
        'show_checklist_prevencao',
        'is_active',
    ];

    protected $casts = [
        'show_sinistro' => 'boolean',
        'show_reserva' => 'boolean',
        'show_avarias' => 'boolean',
        'show_checklist_prevencao' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(OsCategoria::class, 'os_categoria_id');
    }

    public function campos()
    {
        return $this->hasMany(OsSubcategoriaCampo::class, 'os_subcategoria_id');
    }
}
