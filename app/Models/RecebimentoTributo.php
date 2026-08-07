<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecebimentoTributo extends Model
{
    protected $table = 'recebimentos_tributos';

    protected $fillable = [
        'transacao_id',
        'tributo',
        'valor',
    ];

    protected $casts = [
        'valor' => 'float',
    ];

    public function transacao(): BelongsTo
    {
        return $this->belongsTo(Transacao::class, 'transacao_id');
    }
}
