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
        'foto_direita_path',
        'foto_esquerda_path',
        'foto_frente_path',
        'foto_tras_path',
        'foto_velocimetro_path',
        'foto_combustivel_path',
        'assinatura',
        'data_assinatura',
        'assinado',
        'pdf_path',
        'aceite_termo',
        'termo_texto',
        'signatario_nome',
        'signatario_cpf',
        'assinatura_ip',
        'assinatura_dispositivo',
        'pdf_hash',
        // Campos de upgrade de categoria
        'grupo_frota_original_id',
        'veiculo_previsto_id',
        'houve_upgrade_categoria',
        'motivo_upgrade',
        'usuario_upgrade_id',
        'data_hora_upgrade',
    ];

    protected $casts = [
        'houve_upgrade_categoria' => 'boolean',
        'data_hora_upgrade'       => 'datetime',
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

    public function grupoFrotaOriginal()
    {
        return $this->belongsTo(GrupoFrota::class, 'grupo_frota_original_id');
    }

    public function veiculoPrevisto()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_previsto_id');
    }

    public function usuarioUpgrade()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_upgrade_id');
    }

    public function calcao()
    {
        return $this->hasOne(Calcao::class, 'contrato_checkin_id');
    }
}
