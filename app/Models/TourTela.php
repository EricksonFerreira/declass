<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourTela extends Model
{
    use HasAuditColumns;

    protected $table = 'tour_telas';

    protected $fillable = ['chave', 'nome_exibicao', 'usuinc', 'datainc', 'usuatu', 'datatu'];

    protected $casts = [
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function realizacoes(): HasMany
    {
        return $this->hasMany(TourRealizacao::class);
    }
}
