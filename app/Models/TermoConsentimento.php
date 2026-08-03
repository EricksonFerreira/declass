<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermoConsentimento extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'termo_consentimento';
    protected $fillable = [
        'nome',
        'ativo',
        'texto',
        'empresa_id',
        'ordem',
        'titulo',
        'tipo',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem');
    }
}
