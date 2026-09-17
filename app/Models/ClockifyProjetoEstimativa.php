<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClockifyProjetoEstimativa extends Model
{
    protected $table = 'clockify_projeto_estimativas';

    protected $fillable = [
        'empresa_id',
        'clockify_projeto_id',
        'funcao_id',
        'horas_estimadas',
    ];

    protected $casts = [
        'horas_estimadas' => 'float',
    ];

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(ClockifyProjeto::class, 'clockify_projeto_id');
    }

    public function funcao(): BelongsTo
    {
        return $this->belongsTo(Funcao::class, 'funcao_id');
    }
}
