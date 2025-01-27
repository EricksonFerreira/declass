<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratoCheckin extends Model
{
    use HasFactory;

    protected $table = 'contrato_checkin';
    protected $fillable = [
        'reserva_id',
        'data_saida',
        'hora_saida',
        'marca_id',
        'modelo_id',
        'nivel_combustivel',
        'notas_saida',
        'observacoes_saida',
        'quilometragem_atual',
        'veiculo_id',
    ];
    public $timestamps = true;

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'modelo_id');
    }
}
