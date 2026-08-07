<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperacaoFiscal extends Model
{
    protected $table = 'operacoes_fiscais';

    protected $fillable = [
        'empresa_id',
        'locacao_id',
        'fatura_id',
        'tipo',
        'data_fato_gerador',
        'valor_operacao',
        'valor_base_calculo',
        'status',
    ];

    protected $casts = [
        'data_fato_gerador' => 'datetime',
        'valor_operacao' => 'float',
        'valor_base_calculo' => 'float',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function locacao(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'locacao_id');
    }

    public function tributos(): HasMany
    {
        return $this->hasMany(OperacaoFiscalTributo::class, 'operacao_fiscal_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoFiscal::class, 'operacao_fiscal_id');
    }
}
