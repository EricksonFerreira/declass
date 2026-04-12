<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    use HasFactory;

    protected $table = 'modelos';
    protected $fillable = [
        'empresa_id',
        'nome',
        'nome_fipe',
        'codigo_fipe',
        'imagem',
        'ativo',
        'marca_id',
        'segmento_id',
    ];

    public function getImagemUrlAttribute(): ?string
    {
        $path = is_string($this->imagem ?? null) ? trim($this->imagem) : '';
        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    }

    public function getConcatenadoAttribute()
    {
        return "{$this->nome} -> {$this->marcas?->nome} -> {$this->segmentos?->nome}";
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'modelo_id');
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
