<?php
namespace App\Models;

use App\Models\Concerns\HasAuditColumns;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasAuditColumns;

    protected $table = 'horarios';

    protected $fillable = [
        'estacao_id',
        'horario_diario_id',
        'hora_abertura',
        'hora_fechamento',
        'hora_inicio_almoco',
        'hora_fim_almoco',
        'hora_inicio_fora_expediente',
        'hora_fim_fora_expediente',
        'funciona_24h',
        'funciona',
        'tem_intervalo_almoco',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'funciona_24h' => 'boolean',
        'funciona' => 'boolean',
        'tem_intervalo_almoco' => 'boolean',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    // Relacionamento com Estacao
    public function estacao()
    {
        return $this->belongsTo(Estacao::class, 'estacao_id');
    }

    // Relacionamento com HorarioDiario
    public function horarioDiario()
    {
        return $this->belongsTo(HorarioDiario::class, 'horario_diario_id');
    }
}
