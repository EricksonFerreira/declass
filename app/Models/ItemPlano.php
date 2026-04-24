<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPlano extends Model
{
    protected $table = 'itens_plano';

    protected $fillable = [
        'plano_id',
        'descricao',
        'incluso',
        'ordem',
    ];

    protected $casts = [
        'incluso' => 'boolean',
    ];

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }
}
