<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Sinistro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sinistros';

    protected $fillable = [
        'empresa_id',
        'veiculo_id',
        'reserva_id',
        'reserva_motorista_app_id',
        'cliente_id',
        'condutor_id',
        'responsavel_id',
        'tipo_sinistro',
        'data_hora_ocorrido',
        'local_ocorrido',
        'descricao',
        'placa_momento',
        'numero_bo',
        'orgao_bo',
        'terceiros_envolvidos',
        'terceiro_nome',
        'terceiro_telefone',
        'terceiro_placa',
        'vitimas',
        'seguradora',
        'numero_apolice',
        'numero_sinistro_seguradora',
        'franquia',
        'valor_prejuizo_estimado',
        'valor_aprovado_seguradora',
        'valor_responsabilidade_cliente',
        'destino_custo',
        'valor_custo_empresa',
        'forma_pagamento_empresa',
        'pago_empresa_em',
        'pago_empresa_por_user_id',
        'referencia_pagamento_empresa',
        'forma_pagamento_cliente',
        'pago_cliente_em',
        'pago_cliente_por_user_id',
        'referencia_pagamento_cliente',
        'status',
        'observacoes',
        'anexo_bo_path',
        'anexo_foto1_path',
        'anexo_foto2_path',
        'anexo_foto3_path',
        'anexo_laudo_path',
    ];

    protected $casts = [
        'data_hora_ocorrido'             => 'datetime',
        'terceiros_envolvidos'           => 'boolean',
        'vitimas'                        => 'boolean',
        'franquia'                       => 'decimal:2',
        'valor_prejuizo_estimado'        => 'decimal:2',
        'valor_aprovado_seguradora'      => 'decimal:2',
        'valor_responsabilidade_cliente' => 'decimal:2',
        'valor_custo_empresa'            => 'decimal:2',
        'pago_empresa_em'                => 'datetime',
        'pago_cliente_em'                => 'datetime',
    ];

    const FORMAS_PAGAMENTO = [
        'dinheiro'       => 'Dinheiro (cédulas)',
        'cartao_credito' => 'Cartão de Crédito',
        'cartao_debito'  => 'Cartão de Débito',
        'pix'            => 'PIX',
        'boleto'         => 'Boleto',
        'transferencia'  => 'Transferência Bancária',
    ];

    const DESTINO_CUSTO = [
        'empresa' => 'Empresa (débito interno)',
        'cliente' => 'Cliente (cobrar na reserva)',
        'ambos'   => 'Ambos (empresa + cliente)',
    ];

    const TIPOS = [
        'colisao'      => 'Colisão',
        'furto_roubo'  => 'Furto / Roubo',
        'incendio'     => 'Incêndio',
        'vandalismo'   => 'Vandalismo',
        'alagamento'   => 'Alagamento',
        'outros'       => 'Outros',
    ];

    const STATUS = [
        'aberto'                => 'Aberto',
        'em_analise'            => 'Em Análise',
        'aguardando_documentos' => 'Aguardando Documentos',
        'aprovado'              => 'Aprovado',
        'indenizado'            => 'Indenizado',
        'encerrado'             => 'Encerrado',
        'cancelado'             => 'Cancelado',
    ];

    // ---- Relationships ----

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function reservaMotoristaApp()
    {
        return $this->belongsTo(ReservaMotoristaApp::class, 'reserva_motorista_app_id');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function condutor()
    {
        return $this->belongsTo(User::class, 'condutor_id');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function pagoEmpresaPor()
    {
        return $this->belongsTo(User::class, 'pago_empresa_por_user_id');
    }

    public function pagoClientePor()
    {
        return $this->belongsTo(User::class, 'pago_cliente_por_user_id');
    }

    public function historico()
    {
        return $this->hasMany(SinistroHistorico::class, 'sinistro_id')->latest();
    }

    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class, 'sinistro_id');
    }

    // ---- Accessors ----

    public function getLabelTipoAttribute(): string
    {
        return self::TIPOS[$this->tipo_sinistro] ?? ucfirst($this->tipo_sinistro);
    }

    public function getLabelStatusAttribute(): string
    {
        return self::STATUS[$this->status] ?? ucfirst($this->status);
    }

    public function getAnexoBoUrlAttribute(): ?string
    {
        return $this->anexo_bo_path ? Storage::url($this->anexo_bo_path) : null;
    }

    public function getAnexoFoto1UrlAttribute(): ?string
    {
        return $this->anexo_foto1_path ? Storage::url($this->anexo_foto1_path) : null;
    }

    public function getAnexoFoto2UrlAttribute(): ?string
    {
        return $this->anexo_foto2_path ? Storage::url($this->anexo_foto2_path) : null;
    }

    public function getAnexoFoto3UrlAttribute(): ?string
    {
        return $this->anexo_foto3_path ? Storage::url($this->anexo_foto3_path) : null;
    }

    public function getAnexoLaudoUrlAttribute(): ?string
    {
        return $this->anexo_laudo_path ? Storage::url($this->anexo_laudo_path) : null;
    }
}
