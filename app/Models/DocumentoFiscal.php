<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoFiscal extends Model
{
    protected $table = 'documentos_fiscais';

    protected $fillable = [
        'empresa_id',
        'operacao_fiscal_id',
        'tipo',
        'numero_dps',
        'numero_nfse',
        'chave_acesso',
        'codigo_tributacao',
        'status',
        'xml',
        'xml_retorno',
        'protocolo',
        'erro_codigo',
        'erro_mensagem',
        'emitida_em',
        'cancelada_em',
    ];

    protected $casts = [
        'emitida_em' => 'datetime',
        'cancelada_em' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function operacaoFiscal(): BelongsTo
    {
        return $this->belongsTo(OperacaoFiscal::class, 'operacao_fiscal_id');
    }
}
