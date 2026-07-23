<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OsCategoria extends Model
{
    protected $table = 'os_categorias';

    protected $fillable = [
        'nome',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subcategorias(): HasMany
    {
        return $this->hasMany(OsSubcategoria::class, 'os_categoria_id');
    }
}
