<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesquisaPergunta extends Model
{
    protected $fillable = [
        'pesquisa_id',
        'ordem',
        'pergunta',
        'tipo',
        'obrigatoria',
    ];

    public const TIPOS = [
        'nota' => 'Nota de 1 a 5',
        'sim_nao' => 'Sim / Não',
        'texto' => 'Texto livre',
    ];

    public function pesquisa(): BelongsTo
    {
        return $this->belongsTo(Pesquisa::class, 'pesquisa_id');
    }

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }
}
