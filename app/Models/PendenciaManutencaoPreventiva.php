<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendenciaManutencaoPreventiva extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'pendencias_manutencao_preventiva';

    protected $fillable = [
        'empresa_id',
        'veiculo_id',
        'item_id',
        'data_prevista',
        'km_previsto',
        'status', // pendente, atrasado
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'data_prevista' => 'datetime',
        'km_previsto' => 'integer',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
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
