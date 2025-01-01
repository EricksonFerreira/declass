<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoFrota extends Model
{
    use HasFactory;

    protected $table = 'grupos_frota';
    protected $fillable = [
        'tipo_frota_id',
        'codigo',
        'codigo_sipp',
        'nome',
        'valor_diario',
        'imagem',
        'ativo',
    ];

    public function modelos()
    {
        return $this->belongsToMany(Modelo::class, 'grupo_frota_modelo', 'grupo_frota_id', 'modelo_id');
    }

    public function caracteristicas()
    {
        return $this->belongsToMany(CaracteristicaFrota::class, 'grupo_frota_caracteristica_frota', 'grupo_frota_id', 'caracteristica_frota_id')
            ->withPivot('valor');;
    }

    public function opcionais()
    {
        return $this->belongsToMany(Opcional::class, 'grupo_frota_opcional', 'grupo_frota_id', 'opcional_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'grupo_frota_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($grupoFrota) {
            $grupoFrota->caracteristicas()->delete();
        });
    }
}
