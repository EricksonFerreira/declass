<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemChecklist extends Model
{
    use HasFactory;

    protected $table = 'item_checklist';
    protected $fillable = [
        'nome',
        'ativo',
    ];

    public function configuracaoChecklist()
    {
        return $this->hasMany(ConfiguracaoChecklist::class);
    }
}
