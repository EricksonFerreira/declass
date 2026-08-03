<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisponibilidadeInicialVeiculo extends Model
{
    use HasFactory, HasAuditColumns;

    protected $table = 'disponibilidade_inicial_veiculo';
    protected $fillable = [
        'veiculo_id',
        'estacao_id',
        'data_hora',
        'efetuou',
        'created_at',
        'updated_at',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

}
