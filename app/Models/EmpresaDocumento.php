<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaDocumento extends Model
{
    protected $table = 'empresa_documentos';

    protected $fillable = [
        'empresa_id',
        'tipo',
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

    public const TIPOS = [
        'crea_empresa' => 'CREA/CAU da empresa',
        'crea_responsavel' => 'CREA/CAU do responsável técnico',
        'certidao_federal' => 'Certidão Federal/INSS (CND)',
        'certidao_fgts' => 'Certidão FGTS (CRF)',
        'certidao_trabalhista' => 'Certidão Trabalhista (CNDT)',
        'certidao_estadual' => 'Certidão Estadual',
        'certidao_municipal' => 'Certidão Municipal',
        'balanco' => 'Balanço patrimonial',
        'atestado' => 'Atestado de capacidade técnica (CAT)',
        'certificacao' => 'Certificação (ISO, PBQP-H…)',
        'alvara' => 'Alvará/Registro',
        'contrato_social' => 'Contrato social/alterações',
        'outro' => 'Outro',
    ];

    public const SITUACOES = [
        'vigente' => 'Vigente',
        'vencendo' => 'Vence em breve',
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
