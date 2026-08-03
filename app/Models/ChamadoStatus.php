<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;

class ChamadoStatus extends Model
{
    use HasAuditColumns;

    protected $fillable = ['empresa_id', 'nome', 'slug', 'cor', 'ordem', 'ativo', 'padrao_entrada', 'padrao_atendimento', 'padrao_fechamento', 'usuinc', 'datainc', 'usuatu', 'datatu'];

    protected $casts = [
        'ativo' => 'boolean',
        'padrao_entrada' => 'boolean',
        'padrao_atendimento' => 'boolean',
        'padrao_fechamento' => 'boolean',
        'ordem' => 'integer',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];
}
