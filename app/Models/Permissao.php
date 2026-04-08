<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permissao extends Model
{
    use HasFactory;

    protected $table = 'permissions';
    protected $fillable = [
        'name',
        'guard_name',
        'descricao',
        // empresa_id intencionalmente omitido — permissões são globais
    ];

    public function regras()
    {
        return $this->belongsToMany(Regra::class, 'role_has_permissions', 'permission_id', 'role_id');
    }
}
