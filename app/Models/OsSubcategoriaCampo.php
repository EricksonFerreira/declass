<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OsSubcategoriaCampo extends Model
{
    protected $table = 'os_subcategoria_campos';

    protected $fillable = [
        'os_subcategoria_id',
        'nome',
        'tipo',
        'is_required',
        'opcoes',
        'ordem',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'opcoes' => 'array',
        'ordem' => 'integer',
    ];

    public function subcategoria(): BelongsTo
    {
        return $this->belongsTo(OsSubcategoria::class, 'os_subcategoria_id');
    }

    public function valores(): HasMany
    {
        return $this->hasMany(OrdemServicoCampoValor::class, 'os_subcategoria_campo_id');
    }
}
