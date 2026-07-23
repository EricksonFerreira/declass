<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendenciaManutencaoPreventiva extends Model
{
    use HasFactory;

    protected $table = 'pendencias_manutencao_preventiva';

    protected $fillable = [
        'empresa_id',
        'veiculo_id',
        'item_id',
        'data_prevista',
        'km_previsto',
        'status', // pendente, atrasado
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function item()
    {
        return $this->belongsTo(ItemManutencaoPreventiva::class, 'item_id');
    }
}
