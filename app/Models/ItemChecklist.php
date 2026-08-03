<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemChecklist extends Model
{
    use HasAuditColumns, HasFactory;

    protected $table = 'item_checklist';
    protected $fillable = [
        'nome',
        'ativo',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function configuracaoChecklist()
    {
        return $this->hasMany(ConfiguracaoChecklist::class);
    }
}
