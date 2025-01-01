<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
