<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaFinanceira extends Model
{
    use HasFactory, HasAuditColumns;

    protected $table = 'categorias_financeiras';

    protected $fillable = [
        'empresa_id',
        'categoria_pai_id',
        'codigo',
        'nome',
        'tipo',
        'descricao',
        'status',
        'permite_lancamento',
        'origem',
        'usuinc',
        'datainc',
        'usuatu',
        'datatu',
    ];

    protected $casts = [
        'permite_lancamento' => 'boolean',
        'datainc' => 'datetime',
        'datatu' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function categoriaPai()
    {
        return $this->belongsTo(CategoriaFinanceira::class, 'categoria_pai_id');
    }

    public function subcategorias()
    {
        return $this->hasMany(CategoriaFinanceira::class, 'categoria_pai_id');
    }

    public function lancamentos()
    {
        return $this->hasMany(LancamentoFinanceiro::class, 'categoria_financeira_id');
    }

    public function getHasLancamentosAttribute()
    {
        return $this->lancamentos()->exists();
    }

    public static function sugerirCodigo($empresaId, $tipo, $categoriaPaiId = null)
    {
        $prefix = $tipo === 'receita' ? 'R' : 'D';

        if ($categoriaPaiId) {
            $pai = self::find($categoriaPaiId);
            if ($pai) {
                $parentCode = $pai->codigo ?: ($prefix . '.01');
                $maxSub = self::where('empresa_id', $empresaId)
                    ->where('categoria_pai_id', $categoriaPaiId)
                    ->where('codigo', 'like', $parentCode . '.%')
                    ->orderByRaw('LENGTH(codigo) DESC')
                    ->orderBy('codigo', 'desc')
                    ->first();

                if ($maxSub) {
                    $parts = explode('.', $maxSub->codigo);
                    $lastPart = (int) end($parts);
                    return $parentCode . '.' . sprintf('%03d', $lastPart + 1);
                } else {
                    return $parentCode . '.001';
                }
            }
        }

        $maxTop = self::where('empresa_id', $empresaId)
            ->where('tipo', $tipo)
            ->whereNull('categoria_pai_id')
            ->where('codigo', 'like', $prefix . '.%')
            ->orderByRaw('LENGTH(codigo) DESC')
            ->orderBy('codigo', 'desc')
            ->first();

        if ($maxTop) {
            $parts = explode('.', $maxTop->codigo);
            $lastPart = (int) $parts[1];
            return $prefix . '.' . sprintf('%02d', $lastPart + 1);
        } else {
            return $prefix . '.01';
        }
    }
}
