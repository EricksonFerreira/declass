<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;

class ChamadoCategoria extends Model
{
    use HasAuditColumns;

    protected $fillable = ['empresa_id', 'nome', 'descricao', 'ativo', 'usuinc', 'datainc', 'usuatu', 'datatu'];

    protected $casts = ['ativo' => 'boolean', 'datainc' => 'datetime', 'datatu' => 'datetime'];

    public function subcategorias()
    {
        return $this->hasMany(ChamadoSubcategoria::class, 'chamado_categoria_id');
    }
}
