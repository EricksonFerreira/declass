<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanoMensalidade extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'planos_mensalidade';

    protected $fillable = [
        'empresa_id',
        'nome',
        'valor',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'valor' => 'float',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'plano_mensalidade_id');
    }

    public function mensalidades(): HasMany
    {
        return $this->hasMany(Mensalidade::class, 'plano_mensalidade_id');
    }
}
