<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanoManutencaoPreventiva extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'planos_manutencao_preventiva';

    protected $fillable = [
        'empresa_id',
        'veiculo_id',
        'grupo_frota_id',
        'nome',
        'descricao',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function grupoFrota()
    {
        return $this->belongsTo(GrupoFrota::class);
    }

    public function itens()
    {
        return $this->hasMany(PlanoManutencaoItem::class, 'plano_id');
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
