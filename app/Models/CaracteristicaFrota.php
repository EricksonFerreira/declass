<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaracteristicaFrota extends Model
{
    use HasFactory;

    protected $table = 'caracteristicas_frotas';
    protected $fillable = [
        'codigo',
        'nome',
        'tipo_valor_caracteristica_frota_id',
        'descricao',
        'ativo',
    ];

    public function tipoValorDescricao()
    {
        return $this->belongsTo(TipoValorCaracteristicaFrota::class, 'tipo_valor_caracteristica_frota_id');
    }

    public function tiposFrota()
    {
        return $this->belongsToMany(TipoFrota::class, 'caracteristicas_tipo_frota', 'caracteristica_frota_id', 'tipo_frota_id');
    }


    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($caracteristicaFrota) {
            $caracteristicaFrota->grupos()->delete();
        });
    }
}
