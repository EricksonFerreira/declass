<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanoManutencaoItem extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'plano_manutencao_itens';

    protected $fillable = [
        'plano_id',
        'item_id',
        'frequencia_meses',
        'frequencia_km',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'frequencia_meses' => 'integer',
        'frequencia_km' => 'integer',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
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
