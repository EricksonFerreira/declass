<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VeiculoDanosImagem extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'veiculo_danos_imagens';
    protected $fillable = [
        'dano_veiculo_id',
        'caminho_imagem',
        'nome_imagem',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function dano()
    {
        return $this->belongsTo(VeiculoDanos::class, 'dano_veiculo_id');
    }
}
