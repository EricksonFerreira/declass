<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamadoSubcategoria extends Model
{
    protected $fillable = ['empresa_id', 'chamado_categoria_id', 'nome', 'descricao', 'prazo_horas', 'ativo'];

    protected $casts = ['ativo' => 'boolean', 'prazo_horas' => 'integer'];

    public function categoria()
    {
        return $this->belongsTo(ChamadoCategoria::class, 'chamado_categoria_id');
    }
}
