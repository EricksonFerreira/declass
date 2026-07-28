<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanoManutencaoPreventiva extends Model
{
    use HasFactory;

    protected $table = 'planos_manutencao_preventiva';

    protected $fillable = [
        'empresa_id',
        'veiculo_id',
        'grupo_frota_id',
        'nome',
        'descricao',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function grupoFrota()
    {
        return $this->belongsTo(GrupoFrota::class);
    }

    public function itens()
    {
        return $this->hasMany(PlanoManutencaoItem::class, 'plano_id');
    }
}
