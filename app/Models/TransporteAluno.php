<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Passageiro do transporte: aluno em uma rota (e parada).
 */
class TransporteAluno extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'transporte_alunos';

    protected $fillable = [
        'empresa_id',
        'aluno_id',
        'rota_id',
        'parada_id',
        'ativo',
        'observacoes',
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function rota(): BelongsTo
    {
        return $this->belongsTo(RotaTransporte::class, 'rota_id');
    }

    public function parada(): BelongsTo
    {
        return $this->belongsTo(ParadaTransporte::class, 'parada_id');
    }

    public function estaAtivo(): bool
    {
        return $this->ativo === 'S';
    }
}
