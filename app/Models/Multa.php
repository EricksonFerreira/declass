<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Multa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'multas';

    protected $fillable = [
        'empresa_id',
        'reserva_id',
        'veiculo_id',
        'cliente_id',
        'condutor_id',
        'placa_momento',
        'numero_auto',
        'orgao_autuador',
        'data_hora_infracao',
        'local_infracao',
        'descricao',
        'valor_original',
        'valor_desconto',
        'valor_final',
        'pontos',
        'data_vencimento',
        'status_multa',
        'status_desconto',
        'responsavel_lancamento_id',
        'observacao',
        'destino_cobranca',
        'forma_pagamento',
        'pago_em',
        'pago_por_user_id',
        'referencia_pagamento',
        'anexo_comprovante_path',
        'anexo_pdf_path',
    ];

    const DESTINO_COBRANCA = [
        'empresa' => 'Empresa (débito interno)',
        'cliente' => 'Cliente (cobrar na reserva)',
    ];

    const FORMAS_PAGAMENTO = [
        'dinheiro'        => 'Dinheiro (cédulas)',
        'cartao_credito'  => 'Cartão de Crédito',
        'cartao_debito'   => 'Cartão de Débito',
        'pix'             => 'PIX',
        'boleto'          => 'Boleto',
        'debito_automatico' => 'Débito Automático',
    ];

    protected $casts = [
        'data_hora_infracao'   => 'datetime',
        'data_vencimento'      => 'date',
        'valor_original'       => 'decimal:2',
        'valor_desconto'       => 'decimal:2',
        'valor_final'          => 'decimal:2',
        'pago_em'              => 'datetime',
    ];

    const STATUS_MULTA = [
        'pendente'                       => 'Pendente',
        'em_analise'                     => 'Em Analise',
        'aguardando_identificacao_condutor' => 'Aguardando Identificacao do Condutor',
        'recorrida'                      => 'Recorrida',
        'deferida'                       => 'Deferida',
        'indeferida'                     => 'Indeferida',
        'paga'                           => 'Paga',
        'cancelada'                      => 'Cancelada',
    ];

    const STATUS_DESCONTO = [
        'nao_aplicado' => 'Não Aplicado',
        'pendente'     => 'Pendente',
        'descontado'   => 'Descontado',
        'nao_descontar' => 'Nao Descontar',
        'estornado'    => 'Estornado',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function condutor()
    {
        return $this->belongsTo(User::class, 'condutor_id');
    }

    public function pagoPor()
    {
        return $this->belongsTo(User::class, 'pago_por_user_id');
    }

    public function responsavelLancamento()
    {
        return $this->belongsTo(User::class, 'responsavel_lancamento_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function historico()
    {
        return $this->hasMany(MultaHistorico::class, 'multa_id')->latest();
    }

    public function getAnexoComprovanteUrlAttribute(): ?string
    {
        if (!$this->anexo_comprovante_path) {
            return null;
        }
        return Storage::url($this->anexo_comprovante_path);
    }

    public function getAnexoPdfUrlAttribute(): ?string
    {
        if (!$this->anexo_pdf_path) {
            return null;
        }
        return Storage::url($this->anexo_pdf_path);
    }

    public function getLabelStatusMultaAttribute(): string
    {
        return self::STATUS_MULTA[$this->status_multa] ?? ucfirst($this->status_multa);
    }

    public function getLabelStatusDescontoAttribute(): string
    {
        return self::STATUS_DESCONTO[$this->status_desconto] ?? ucfirst($this->status_desconto);
    }
}
