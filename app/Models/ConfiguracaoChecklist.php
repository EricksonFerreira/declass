<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoChecklist extends Model
{
    use HasFactory, HasAuditColumns;

    protected $table = 'configuracao_checklist';
    protected $fillable = [
        'tipo_checklist_id',
        'item_checklist_id',
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

    public function tipoChecklist()
    {
        return $this->belongsTo(TipoChecklist::class);
    }

    public function itemChecklist()
    {
        return $this->belongsTo(ItemChecklist::class);
    }
}
