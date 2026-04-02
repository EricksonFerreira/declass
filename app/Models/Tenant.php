<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'nome',
        'slug',
        'ativo',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'empresa_id');
    }
}
