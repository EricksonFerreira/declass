<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimentacaoHistorico extends Model
{
    protected $table = 'movimentacoes_historico';

    protected $fillable = [
        'movimentacao_id',
        'user_id',
        'campo',
        'valor_anterior',
        'valor_novo',
        'acao',
        'observacao',
        'empresa_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function movimentacao()
    {
        return $this->belongsTo(Movimentacao::class, 'movimentacao_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
