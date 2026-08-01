<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamadoLog extends Model
{
    protected $fillable = ['chamado_id', 'user_id', 'acao', 'detalhes', 'nivel'];

    public function chamado()
    {
        return $this->belongsTo(Chamado::class, 'chamado_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
