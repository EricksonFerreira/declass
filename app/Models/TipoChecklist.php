<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ConfiguracaoChecklist;
use App\Models\ItemChecklist;

class TipoChecklist extends Model
{
    use HasFactory;

    protected $table = 'tipo_checklist';
    protected $fillable = [
        'nome',
        'ativo',
    ];

    public function configuracaoChecklist()
    {
        return $this->hasMany(ConfiguracaoChecklist::class);
    }

    public function itensChecklist()
    {
        return $this->hasManyThrough(
            ItemChecklist::class,
            ConfiguracaoChecklist::class,
            'tipo_checklist_id',
            'id',
            'id',
            'item_checklist_id'
        );
    }
}
