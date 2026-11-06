<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaDocumento extends Model
{
    use HasAuditColumns;

    protected $table = 'empresa_documentos';

    protected $fillable = [
        'empresa_id',
        'tipo',
        'titulo',
        'numero',
        'orgao_emissor',
        'descricao',
        'valor',
        'data_emissao',
        'data_validade',
        'arquivo_path',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_validade' => 'date',
        'valor' => 'decimal:2',
    ];

    /**
     * Tipos de documento institucional do tenant escolar.
     */
    public const TIPOS = [
        'contrato_social' => 'Contrato social / alterações',
        'alvara' => 'Alvará de funcionamento',
        'certidao_federal' => 'Certidão Federal / CND',
        'certidao_estadual' => 'Certidão Estadual',
        'certidao_municipal' => 'Certidão Municipal',
        'certidao_trabalhista' => 'Certidão Trabalhista (CNDT)',
        'certidao_fgts' => 'Certidão FGTS (CRF)',
        'autorizacao_funcionamento' => 'Autorização de funcionamento (órgão educacional)',
        'parecer_council' => 'Parecer do Conselho Municipal/Estadual de Educação',
        'regimento' => 'Regimento escolar',
        'projeto_pedagogico' => 'Projeto político-pedagógico (PPP)',
        'outro' => 'Outro',
    ];

    /**
     * Situacao do documento derivada da data de validade.
     */
    public const SITUACOES = [
        'vigente' => 'Vigente',
        'vencendo' => 'Vencendo (30 dias)',
        'vencido' => 'Vencido',
        'sem_validade' => 'Sem validade',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    public function getSituacaoAttribute(): string
    {
        if (! $this->data_validade) {
            return 'sem_validade';
        }

        if ($this->data_validade->isPast()) {
            return 'vencido';
        }

        return $this->data_validade->lte(now()->addDays(30)) ? 'vencendo' : 'vigente';
    }
}
