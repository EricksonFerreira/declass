<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanoManutencaoItem extends Model
{
    use HasFactory;

    protected $table = 'plano_manutencao_itens';

    protected $fillable = [
        'plano_id',
        'item_id',
        'frequencia_meses',
        'frequencia_km',
    ];

    public function plano()
    {
        return $this->belongsTo(PlanoManutencaoPreventiva::class, 'plano_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemManutencaoPreventiva::class, 'item_id');
    }
}
