<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Registro disciplinar/pedagogico sobre um aluno.
 */
class Ocorrencia extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    public const TIPOS = [
        'elogio' => 'Elogio',
        'advertencia' => 'Advertência',
        'ocorrencia' => 'Ocorrência',
        'suspensao' => 'Suspensão',
        'outro' => 'Outro',
    ];

    public const GRAVIDADES = [
        'leve' => 'Leve',
        'media' => 'Média',
        'grave' => 'Grave',
    ];

    protected $fillable = [
        'empresa_id',
        'aluno_id',
        'escola_id',
        'user_id',
        'data',
        'tipo',
        'gravidade',
        'local',
        'descricao',
        'medida_adotada',
        'notifica_responsavel',
        'ativo',
    ];

    protected $casts = [
        'data' => 'date',
        'notifica_responsavel' => 'boolean',
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /** Atalho de leitura em Blade: $model->tipo_label */
    public function getTipoLabelAttribute()
    {
        return $this->tipoLabel();
    }

    public function gravidadeLabel(): string
    {
        return self::GRAVIDADES[$this->gravidade] ?? $this->gravidade;
    }

    /** Atalho de leitura em Blade: $model->gravidade_label */
    public function getGravidadeLabelAttribute()
    {
        return $this->gravidadeLabel();
    }

    public function estaAtivo(): bool
    {
        return $this->ativo === 'S';
    }

    /** Atalho de leitura em Blade: $model->esta_ativo */
    public function getEstaAtivoAttribute()
    {
        return $this->estaAtivo();
    }
}
