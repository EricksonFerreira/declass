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
        'valor',
        'incluso',
        'ordem',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'incluso' => 'boolean',
    ];

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }
}
