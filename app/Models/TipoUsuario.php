<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    use HasFactory;

    protected $table = 'tipo_usuarios';

    protected $fillable = [
        'name',
        'guard_name',
        'ativo',
        'empresa_id',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
