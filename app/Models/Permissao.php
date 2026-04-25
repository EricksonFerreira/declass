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
        'grupo',
        // empresa_id intencionalmente omitido — permissões são globais
    ];

    /**
     * Retorna o grupo derivado automaticamente do nome da permissão se não definido.
     */
    public function getGrupoEfetivoAttribute(): string
    {
        if (!empty($this->grupo)) {
            return $this->grupo;
        }
        if (str_contains($this->name, '.')) {
            return (string) str($this->name)->before('.');
        }
        return $this->name;
    }

    public function regras()
    {
        return $this->belongsToMany(Regra::class, 'role_has_permissions', 'permission_id', 'role_id');
    }

    public function planos()
    {
        return $this->belongsToMany(Plano::class, 'plano_permissao', 'permission_id', 'plano_id')->withTimestamps();
    }
}
