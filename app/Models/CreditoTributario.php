<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditoTributario extends Model
{
    protected $table = 'creditos_tributarios';

    protected $fillable = [
        'empresa_id',
        'documento_fiscal_entrada_id',
        'tributo',
        'valor_original',
        'valor_utilizado',
        'saldo',
        'origem',
        'data_apropriacao',
        'status',
    ];

    protected $casts = [
        'valor_original' => 'float',
        'valor_utilizado' => 'float',
        'saldo' => 'float',
        'data_apropriacao' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
