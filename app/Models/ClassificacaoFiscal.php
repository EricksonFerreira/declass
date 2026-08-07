<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassificacaoFiscal extends Model
{
    protected $table = 'classificacoes_fiscais';

    protected $fillable = [
        'descricao',
        'ncm',
        'tipo_bem',
    ];

    public function veiculos(): HasMany
    {
        return $this->hasMany(Veiculo::class, 'classificacao_fiscal_id');
    }
}
