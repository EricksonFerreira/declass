<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    use HasFactory;

    protected $table = 'modelos';
    protected $fillable = [
        'nome',
        'ativo',
        'marca_id',
        'segmento_id',
    ];

    public function getConcatenadoAttribute()
    {
        return "{$this->nome} -> {$this->marcas?->nome} -> {$this->segmentos?->nome}";
    }

    public function marcas()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function segmentos()
    {
        return $this->belongsTo(Segmento::class, 'segmento_id');
    }

    public function gruposFrota()
    {
        return $this->belongsToMany(GrupoFrota::class, 'grupo_frota_modelo', 'modelo_id', 'grupo_frota_id');
    }
}
