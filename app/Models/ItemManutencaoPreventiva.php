<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemManutencaoPreventiva extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'itens_manutencao_preventiva';

    protected $fillable = [
        'empresa_id',
        'nome',
        'descricao',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
