<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemManutencaoPreventiva extends Model
{
    use HasFactory;

    protected $table = 'itens_manutencao_preventiva';

    protected $fillable = [
        'empresa_id',
        'nome',
        'descricao',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
