<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoChecklist extends Model
{
    use HasFactory;

    protected $table = 'configuracao_checklist';
    protected $fillable = [
        'tipo_checklist_id',
        'item_checklist_id',
        'ativo',
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
