<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaPlano extends Model
{
    protected $table = 'empresa_planos';

    public function plano()
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }
}
