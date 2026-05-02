<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regra extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $fillable = [
        'name',
        'guard_name',
        'empresa_id',
    ];
    public function permissoes()
    {
        return $this->belongsToMany(Permissao::class, 'role_has_permissions', 'role_id', 'permission_id');
    }
}
