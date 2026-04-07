<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinistroHistorico extends Model
{
    protected $table = 'sinistro_historico';

    protected $fillable = [
        'sinistro_id',
        'user_id',
        'campo_alterado',
        'valor_anterior',
        'valor_novo',
        'descricao',
    ];

    public function sinistro()
    {
        return $this->belongsTo(Sinistro::class, 'sinistro_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
