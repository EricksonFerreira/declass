<?php

namespace App\Models;

use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class Opcional extends Model
{
    use ModelTrait;
    protected $table = 'opcionais';

    protected $fillable = [
        'nome',
        'ativo',
        'tipo_cobranca',
        'preco_por_dia',
        'preco_integral',
        'tipo_opcional_id',
        'descricao'
    ];

    // Tipo de dados dos atributos
    protected $casts = [
        'preco_por_dia' => 'decimal:2',
        'preco_integral' => 'decimal:2',
        'tipo_cobranca' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Convertendo vírgula em ponto para preco_por_dia
    public function setPrecoPorDiaAttribute($value)
    {
        $this->attributes['preco_por_dia'] = $this->convertToDecimal($value);
    }


    // Convertendo vírgula em ponto para preco_integral
    public function setPrecoIntegralAttribute($value)
    {
        $this->attributes['preco_integral'] = $this->convertToDecimal($value);
    }

    // Relacionamento com o tipo de opcional
    public function tipoOpcional()
    {
        return $this->belongsTo(TipoOpcional::class, 'tipo_opcional_id');
    }

    public function gruposFrota()
    {
        return $this->belongsToMany(GrupoFrota::class, 'grupo_frota_opcional',  'opcional_id', 'grupo_frota_id');
    }

    // Relacionamento com os grupos de veículos
    public function gruposVeiculos()
    {
        return $this->belongsToMany(GrupoFrota::class, 'opcionais_grupos_veiculos', 'opcional_id', 'grupo_veiculo_id');
    }

    // Relacionamento com as reservas
    public function reservas()
    {
        return $this->belongsToMany(Reserva::class, 'reservas_opcionais', 'opcional_id', 'reserva_id')
            ->withPivot('quantidade', 'preco_total');
    }

      protected static function boot()
    {
        parent::boot();

        static::deleting(function ($opcional) {
            $opcional->gruposFrota()->detach();
            $opcional->gruposVeiculos()->detach();
            $opcional->reservas()->detach();
        });
    }
}
