<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaudeAluno extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'saude_alunos';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'aluno_id',
        'tipo_sanguineo',
        'alergias',
        'medicamentos',
        'condicoes_cronicas',
        'contato_emergencia_nome',
        'contato_emergencia_parentesco',
        'contato_emergencia_telefone',
        'observacoes',
        'ativo',
    ];

    public const TIPOS_SANGUINEOS = [
        'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-',
        'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-',
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    public function temAlertas(): bool
    {
        return ! empty($this->alergias) || ! empty($this->condicoes_cronicas) || ! empty($this->medicamentos);
    }

    /** Atalho de leitura em Blade: $model->tem_alertas */
    public function getTemAlertasAttribute()
    {
        return $this->temAlertas();
    }

    public function tipoSanguineoLabel(): string
    {
        return $this->tipo_sanguineo ?: '—';
    }

    /** Atalho de leitura em Blade: $model->tipo_sanguineo_label */
    public function getTipoSanguineoLabelAttribute()
    {
        return $this->tipoSanguineoLabel();
    }
}
