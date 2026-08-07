<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperacaoFiscalTributo extends Model
{
    protected $table = 'operacoes_fiscais_tributos';

    protected $fillable = [
        'operacao_fiscal_id',
        'tributo',
        'cst',
        'base_calculo',
        'aliquota',
        'valor',
    ];

    protected $casts = [
        'base_calculo' => 'float',
        'aliquota' => 'float',
        'valor' => 'float',
    ];

    public function operacaoFiscal(): BelongsTo
    {
        return $this->belongsTo(OperacaoFiscal::class, 'operacao_fiscal_id');
    }
}
