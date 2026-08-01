<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamadoCategoria extends Model
{
    protected $fillable = ['empresa_id', 'nome', 'descricao', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    public function subcategorias()
    {
        return $this->hasMany(ChamadoSubcategoria::class, 'chamado_categoria_id');
    }
}
