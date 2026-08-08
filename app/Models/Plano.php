<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Permissao;

class Plano extends Model
{
    protected $table = 'planos';

    public function scopeAtivos($query)
    {
        return $query;
    }

    public function itens()
    {
        return $this->hasMany(ItemPlano::class, 'plano_id');
    }
    public function permissoes()
    {
        return $this->belongsToMany(Permissao::class, 'plano_permissao', 'plano_id', 'permission_id')
            ->withTimestamps();
    }

}
