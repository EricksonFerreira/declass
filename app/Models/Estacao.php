<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estacao extends Model
{
    use HasFactory;

    protected $table = 'estacoes';

    protected $fillable = [
        'codigo',
        'nome',
        'email',
        'telefone',
        'celular',
        'cidade',
        'numero',
        'zip_code',
        'latitude',
        'endereco',
        'pais_id',
        'longitude',
        'ativo',
        'checkin',
        'checkout',
        'cobrar_na_coleta',
        'cobrar_na_entrega',
        'valido_no_intervalo',
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    public function horarios()
    {
        return $this->belongsToMany(HorarioDiario::class, 'horarios', 'estacao_id', 'horario_diario_id')
            ->withPivot([
                'horario_diario_id',
                'hora_abertura',
                'hora_fechamento',
                'hora_inicio_almoco',
                'hora_fim_almoco',
                'hora_inicio_fora_expediente',
                'hora_fim_fora_expediente',
                'funciona_24h',
            ]);
    }

    public function diasUteis()
    {
        return $this->belongsToMany(DiaUtil::class, 'estacoes_dias_uteis', 'estacao_id', 'dia_util_id');
    }

    public function getHorariosComPivot()
    {
        return $this->horarios->map(function ($horario) {
            foreach ($horario->pivot->getAttributes() as $key => $value) {
                $horario->$key = $value;
            }

            return $horario;
        });
    }

    public function reservasRetirada()
    {
        return $this->hasMany(Reserva::class, 'estacao_retirada_id');
    }

    public function reservasDevolucao()
    {
        return $this->hasMany(Reserva::class, 'estacao_devolucao_id');
    }

    public function temposOrigem()
    {
        return $this->hasMany(TempoEntreEntacao::class, 'estacao_origem_id');
    }

    public function temposDestino()
    {
        return $this->hasMany(TempoEntreEntacao::class, 'estacao_destino_id');
    }
}
