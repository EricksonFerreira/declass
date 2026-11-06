<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Documento digitalizado do aluno (RG, certidoes, atestados, laudos).
 */
class AlunoDocumento extends Model
{
    use HasAuditColumns, HasFactory, SoftDeletes;

    protected $table = 'aluno_documentos';

    protected $fillable = [
        'empresa_id',
        'aluno_id',
        'tipo',
        'titulo',
        'descricao',
        'arquivo_path',
    ];

    public const TIPOS = [
        'rg' => 'RG / Identidade',
        'cpf' => 'CPF',
        'certidao_nascimento' => 'Certidão de nascimento',
        'comprovante_residencia' => 'Comprovante de residência',
        'historico_escolar' => 'Histórico escolar',
        'transferencia' => 'Declaração de transferência',
        'atestado_medico' => 'Atestado médico',
        'laudo' => 'Laudo / avaliação',
        'vacinas' => 'Carteira de vacinas',
        'declaracao' => 'Declaração',
        'outro' => 'Outro',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }
}
