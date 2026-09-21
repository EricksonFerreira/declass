<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TutorialCategoria extends Model
{
    protected $table = 'tutorial_categorias';

    protected $fillable = [
        'nome',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function tutoriais(): HasMany
    {
        return $this->hasMany(Tutorial::class, 'tutorial_categoria_id');
    }
}
