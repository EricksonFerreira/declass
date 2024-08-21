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
                        'hora_inicio_almoço',
                        'hora_fim_almoço',
                        'hora_inicio_fora_expediente',
                        'hora_fim_fora_expediente'
                    ]);
    }
    public function diasUteis()
    {
        return $this->belongsToMany(DiaUtil::class, 'estacoes_dias_uteis', 'estacao_id', 'dia_util_id');
    }

   // Nova função getHorariosComPivot
   public function getHorariosComPivot()
   {
       return $this->horarios->map(function ($horario) {
           // Adiciona os valores do pivot diretamente aos atributos
           foreach ($horario->pivot->getAttributes() as $key => $value) {
               $horario->$key = $value;
           }
           return $horario;
       });
   }
}
