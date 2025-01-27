<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VeiculoDanosImagem extends Model
{
    use HasFactory;

    protected $table = 'veiculo_danos_imagens';
    protected $fillable = [
        'dano_veiculo_id',
        'caminho_imagem',
        'nome_imagem',
    ];
    protected $timestamps = true;

    public function dano()
    {
        return $this->belongsTo(VeiculoDanos::class, 'dano_veiculo_id');
    }
}
