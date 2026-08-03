<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;

class ChamadoSubcategoria extends Model
{
    use HasAuditColumns;

    protected $fillable = ['empresa_id', 'chamado_categoria_id', 'nome', 'descricao', 'prazo_horas', 'ativo', 'usuinc', 'datainc', 'usuatu', 'datatu'];

    protected $casts = ['ativo' => 'boolean', 'prazo_horas' => 'integer', 'datainc' => 'datetime', 'datatu' => 'datetime'];

    public function categoria()
    {
        return $this->belongsTo(ChamadoCategoria::class, 'chamado_categoria_id');
    }
}
