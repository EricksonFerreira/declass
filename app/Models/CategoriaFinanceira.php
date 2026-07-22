<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaFinanceira extends Model
{
    use HasFactory;

    protected $table = 'categorias_financeiras';

    protected $fillable = [
        'empresa_id',
        'nome',
        'tipo',
        'descricao',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function lancamentos()
    {
        return $this->hasMany(LancamentoFinanceiro::class, 'categoria_financeira_id');
    }
}
