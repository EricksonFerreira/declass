<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdemServicoCampoValor extends Model
{
    protected $table = 'ordem_servico_campo_valores';

    protected $fillable = [
        'ordem_servico_id',
        'os_subcategoria_campo_id',
        'valor',
    ];

    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function campo(): BelongsTo
    {
        return $this->belongsTo(OsSubcategoriaCampo::class, 'os_subcategoria_campo_id');
    }
}
