<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Plano;

class EmpresaContrato extends Model
{
    use HasFactory;

    protected $table = 'empresa_contratos';

    protected $fillable = [
        'empresa_id',
        'numero',
        'titulo',
        'status',
        'snapshot',
        'conteudo_html',
        'arquivo_pdf_path',
        'gerado_em',
        'gerado_por',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'gerado_em' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }
}
