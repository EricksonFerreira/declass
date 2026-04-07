<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoCalcao extends Model
{
    use HasFactory;

    protected $table = 'configuracoes_calcao';

    protected $fillable = [
        'empresa_id',
        'grupo_frota_id',
        'modelo_id',
        'tipo_regra_valor',
        'valor',
        'valor_minimo',
        'valor_maximo',
        'ativo',
    ];

    protected $casts = [
        'valor'        => 'decimal:2',
        'valor_minimo' => 'decimal:2',
        'valor_maximo' => 'decimal:2',
        'ativo'        => 'boolean',
    ];

    const TIPO_FIXO       = 'fixo';
    const TIPO_PERCENTUAL = 'percentual';

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function grupoFrota()
    {
        return $this->belongsTo(GrupoFrota::class, 'grupo_frota_id');
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'modelo_id');
    }

    /**
     * Retorna o nivel de prioridade: modelo > grupo > empresa
     */
    public function getNivelAttribute(): string
    {
        if ($this->modelo_id) {
            return 'modelo';
        }
        if ($this->grupo_frota_id) {
            return 'grupo';
        }
        return 'empresa';
    }
}
