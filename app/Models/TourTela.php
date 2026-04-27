<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourTela extends Model
{
    protected $table = 'tour_telas';

    protected $fillable = ['chave', 'nome_exibicao'];

    public function realizacoes(): HasMany
    {
        return $this->hasMany(TourRealizacao::class);
    }
}
