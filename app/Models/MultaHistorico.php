<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultaHistorico extends Model
{
    protected $table = 'multa_historico';

    protected $fillable = [
        'multa_id',
        'user_id',
        'campo_alterado',
        'valor_anterior',
        'valor_novo',
        'descricao',
    ];

    public function multa()
    {
        return $this->belongsTo(Multa::class, 'multa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
