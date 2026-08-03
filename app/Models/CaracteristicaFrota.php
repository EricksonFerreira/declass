<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CaracteristicaFrota extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'caracteristicas_frotas';
    protected $fillable = [
        'codigo',
        'nome',
        'tipo_valor_caracteristica_frota_id',
        'descricao',
        'imagem',
        'ativo',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
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

        return Storage::url($path);
    }

    public function tipoValorDescricao()
    {
        return $this->belongsTo(TipoValorCaracteristicaFrota::class, 'tipo_valor_caracteristica_frota_id');
    }

    public function tiposFrota()
    {
        return $this->belongsToMany(TipoFrota::class, 'caracteristicas_tipo_frota', 'caracteristica_frota_id', 'tipo_frota_id');
    }

    public function grupos()
    {
        return $this->belongsToMany(GrupoFrota::class, 'grupo_frota_caracteristica_frota', 'caracteristica_frota_id', 'grupo_frota_id')
            ->withPivot('valor');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($caracteristicaFrota) {
            $caracteristicaFrota->grupos()->detach();
        });
    }
}
