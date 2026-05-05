<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VeiculoDanos extends Model
{
    use HasFactory;
    protected $table = 'veiculo_danos';
    protected $fillable = [
        'categoria_id',
        'localizacao_problema',
        'dano_id',
        'tipo_dano',
        'local_dano',
        'custo_estimado',
        'quilometragem_veiculo',
        'observacoes',
        'veiculo_id',
        'reserva_id',
        'contrato_id',
        'correcao_descricao',
        'correcao_data',
        'correcao_user_id',
    ];
    public $timestamps = true;

    protected $casts = [
        'correcao_data' => 'datetime',
    ];

    public function dano()
    {
        return $this->belongsTo(Dano::class, 'dano_id');
    }
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaDano::class, 'categoria');
    }

    public function imagem()
    {
        return $this->hasMany(VeiculoDanosImagem::class, 'dano_veiculo_id');
    }

    public function usuarioCorrecao()
    {
        return $this->belongsTo(User::class, 'correcao_user_id');
    }

    public function scopeSemCorrecao($query)
    {
        return $query->whereNull('correcao_data');
    }

    public function scopeComCorrecao($query)
    {
        return $query->whereNotNull('correcao_data');
    }
}
