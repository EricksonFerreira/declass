<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourRealizacao extends Model
{
    protected $table = 'tour_realizacoes';

    protected $fillable = ['user_id', 'tour_tela_id', 'feito', 'feito_at'];

    protected $casts = ['feito' => 'boolean', 'feito_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tourTela(): BelongsTo
    {
        return $this->belongsTo(TourTela::class);
    }
}
