<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClockifyProjetoPlanejamento extends Model
{
    protected $table = 'clockify_projeto_planejamentos';

    protected $fillable = [
        'clockify_projeto_cronograma_id',
        'data',
        'horas',
        'eh_extra',
    ];

    protected $casts = [
        'data' => 'date',
        'horas' => 'float',
        'eh_extra' => 'boolean',
    ];

    public function cronograma(): BelongsTo
    {
        return $this->belongsTo(ClockifyProjetoCronograma::class, 'clockify_projeto_cronograma_id');
    }
}
