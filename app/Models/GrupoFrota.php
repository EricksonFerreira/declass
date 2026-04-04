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
        'imagens',
        'ativo',
    ];

    protected $casts = [
        'imagens' => 'array',
    ];

    public function getImagemUrlAttribute(): ?string
    {
        $path = is_string($this->imagem ?? null) ? trim($this->imagem) : '';
        if ($path === '') {
            return $this->getImagensUrlsAttribute()[0] ?? null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    }

    public function getImagensUrlsAttribute(): array
    {
        $imagens = is_array($this->imagens ?? null) ? $this->imagens : [];
        $urls = [];
        foreach ($imagens as $imagem) {
            $path = is_string($imagem) ? trim($imagem) : '';
            if ($path === '') {
                continue;
            }
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
                $urls[] = $path;
                continue;
            }
            $urls[] = \Illuminate\Support\Facades\Storage::url($path);
        }

        return $urls;
    }

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
            $grupoFrota->caracteristicas()->detach();
            $grupoFrota->opcionais()->detach();
            $grupoFrota->modelos()->detach();
        });
    }
}
