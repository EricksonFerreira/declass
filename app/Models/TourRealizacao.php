<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourRealizacao extends Model
{
    use HasAuditColumns;

    protected $table = 'tour_realizacoes';

    protected $fillable = ['user_id', 'tour_tela_id', 'feito', 'feito_at', 'usuinc', 'datainc', 'usuatu', 'datatu'];

    protected $casts = ['feito' => 'boolean', 'feito_at' => 'datetime', 'datainc' => 'datetime', 'datatu' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tourTela(): BelongsTo
    {
        return $this->belongsTo(TourTela::class);
    }
}
