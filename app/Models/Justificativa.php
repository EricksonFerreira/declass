<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Atestado/declaracao que justifica ou abona as ausencias do
 * aluno em um periodo. Registrado pela secretaria; o efeito
 * converte as faltas das aulas do periodo.
 */
class Justificativa extends Model
{
    use HasAuditColumns, SoftDeletes;

    public const TIPOS = [
        'atestado_medico' => 'Atestado médico',
        'declaracao' => 'Declaração de compromisso',
        'judicial' => 'Convocação judicial/eleitoral',
        'representacao' => 'Representação oficial',
        'religioso' => 'Guarda religiosa',
        'falecimento' => 'Falecimento de familiar',
        'outro' => 'Outro',
    ];

    public const EFEITOS = [
        'justificada' => 'Falta justificada (mantém registro)',
        'abonada' => 'Falta abonada (anula o registro)',
    ];

    protected $table = 'justificativas';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'matricula_id',
        'disciplina_id',
        'data_inicio',
        'data_fim',
        'tipo',
        'efeito',
        'motivo',
        'arquivo_path',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuinc');
    }

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    public function efeitoLabel(): string
    {
        return self::EFEITOS[$this->efeito] ?? $this->efeito;
    }
}
