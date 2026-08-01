<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chamado extends Model
{
    public const STATUS_ABERTO = 'aguardando_analise';
    public const STATUS_ATENDIMENTO = 'em_atendimento';
    public const STATUS_AGUARDANDO_SOLICITANTE = 'aguardando_solicitante';
    public const STATUS_CANCELADO = 'cancelado';
    public const STATUS_CONCLUIDO = 'concluido';

    protected $fillable = [
        'empresa_id','user_id','criado_por_user_id','chamado_categoria_id','chamado_subcategoria_id','chamado_status_id',
        'descricao','solucao','prazo_atendimento_em','aberto_em','em_atendimento_em','concluido_em','cancelado_em','aguardando_solicitante_em',
        'responsavel_user_id','cancelamento_justificativa',
    ];

    protected $casts = [
        'prazo_atendimento_em' => 'datetime',
        'aberto_em' => 'datetime',
        'em_atendimento_em' => 'datetime',
        'concluido_em' => 'datetime',
        'cancelado_em' => 'datetime',
        'aguardando_solicitante_em' => 'datetime',
    ];

    public function categoria() { return $this->belongsTo(ChamadoCategoria::class, 'chamado_categoria_id'); }
    public function subcategoria() { return $this->belongsTo(ChamadoSubcategoria::class, 'chamado_subcategoria_id'); }
    public function status() { return $this->belongsTo(ChamadoStatus::class, 'chamado_status_id'); }
    public function anexos() { return $this->hasMany(ChamadoAnexo::class, 'chamado_id'); }
    public function logs() { return $this->hasMany(ChamadoLog::class, 'chamado_id'); }
    public function solicitante() { return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(); }
    public function responsavel() { return $this->belongsTo(User::class, 'responsavel_user_id')->withoutGlobalScopes(); }
    public function criador() { return $this->belongsTo(User::class, 'criado_por_user_id')->withoutGlobalScopes(); }
}
