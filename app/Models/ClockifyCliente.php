<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClockifyCliente extends Model
{
    use HasAuditColumns;

    protected $table = 'clockify_clientes';

    protected $fillable = [
        'empresa_id',
        'nome',
        'email',
        'telefone',
        'endereco',
        'ativo',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function projetos(): HasMany
    {
        return $this->hasMany(ClockifyProjeto::class, 'clockify_cliente_id');
    }
}
