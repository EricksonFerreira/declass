<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusItemChecklist extends Model
{
    use HasFactory;

    protected $table = 'status_item_checklist';
    protected $fillable = [
        'nome',
        'ativo',
    ];
}
