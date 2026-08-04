<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaContrato extends Model
{
    use HasFactory;

    protected $table = 'empresa_contratos';

    protected $fillable = [
        'empresa_id',
        'numero',
        'titulo',
        'status',
        'conteudo_html',
        'arquivo_pdf_path',
        'gerado_em',
        'assinado_em',
        'gerado_por',
        'assinado_por',
    ];

    protected $casts = [
        'gerado_em' => 'datetime',
        'assinado_em' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
