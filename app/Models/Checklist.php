<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use HasFactory, HasAuditColumns;

    protected $table = 'checklists';
    protected $fillable = [
        'nome',
        'ativo',
    ];
}
