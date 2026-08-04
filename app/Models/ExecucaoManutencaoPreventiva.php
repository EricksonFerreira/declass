<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExecucaoManutencaoPreventiva extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'execucoes_manutencao_preventiva';

    protected $fillable = [
        'empresa_id',
        'veiculo_id',
        'item_id',
        'ordem_servico_id',
        'data_execucao',
        'km_execucao',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'data_execucao' => 'datetime',
        'km_execucao' => 'integer',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function item()
    {
        return $this->belongsTo(ItemManutencaoPreventiva::class, 'item_id');
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }

    public function usuarioInclusao()
    {
        return $this->belongsTo(User::class, 'usuinc')->withoutGlobalScopes();
    }

    public function usuarioAtualizacao()
    {
        return $this->belongsTo(User::class, 'usuatu')->withoutGlobalScopes();
    }
}
