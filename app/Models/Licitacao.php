<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Licitacao extends Model
{
    protected $table = 'licitacoes';

    protected $fillable = [
        'empresa_id',
        'orgao',
        'objeto',
        'numero_edital',
        'modalidade',
        'criterio_julgamento',
        'data_abertura',
        'valor_estimado',
        'url_edital',
        'texto_edital',
        'status',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'data_abertura' => 'date',
        'valor_estimado' => 'decimal:2',
    ];

    public const STATUS = [
        'cadastrada' => 'Cadastrada',
        'analisada' => 'Analisada',
        'descartada' => 'Descartada',
        'participando' => 'Participando',
        'vencida' => 'Vencida',
        'perdida' => 'Perdida',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function analises(): HasMany
    {
        return $this->hasMany(LicitacaoAnalise::class)->latest();
    }

    public function ultimaAnalise(): HasOne
    {
        return $this->hasOne(LicitacaoAnalise::class)->latestOfMany();
    }
}
