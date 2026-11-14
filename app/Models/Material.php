<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $table = 'materiais';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'nome',
        'categoria',
        'unidade_medida',
        'estoque_atual',
        'estoque_minimo',
        'localizacao',
        'ativo',
    ];

    protected $casts = [
        'estoque_atual' => 'decimal:2',
        'estoque_minimo' => 'decimal:2',
    ];

    public const CATEGORIAS = [
        'papelaria' => 'Papelaria',
        'limpeza' => 'Limpeza',
        'cantina' => 'Cantina',
        'didatico' => 'Material didático',
        'uniforme' => 'Uniforme/EPI',
        'manutencao' => 'Manutenção',
        'outro' => 'Outro',
    ];

    public const UNIDADES = [
        'un' => 'Unidade',
        'cx' => 'Caixa',
        'pct' => 'Pacote',
        'kg' => 'Quilograma',
        'g' => 'Grama',
        'L' => 'Litro',
        'fd' => 'Fardo',
    ];

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class, 'escola_id');
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(MovimentacaoEstoque::class, 'material_id');
    }

    public function categoriaLabel(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? $this->categoria;
    }

    /** Atalho de leitura em Blade: $model->categoria_label */
    public function getCategoriaLabelAttribute()
    {
        return $this->categoriaLabel();
    }

    public function unidadeLabel(): string
    {
        return self::UNIDADES[$this->unidade_medida] ?? $this->unidade_medida;
    }

    public function baixoEstoque(): bool
    {
        return (float) $this->estoque_minimo > 0
            && (float) $this->estoque_atual <= (float) $this->estoque_minimo;
    }

    /** Atalho de leitura em Blade: $model->baixo_estoque */
    public function getBaixoEstoqueAttribute()
    {
        return $this->baixoEstoque();
    }

    public function estoqueFormatado(): string
    {
        $qtd = rtrim(rtrim(number_format((float) $this->estoque_atual, 2, ',', ''), '0'), ',');

        $unidade = self::UNIDADES[$this->unidade_medida] ?? $this->unidade_medida;

        return $qtd.' '.$unidade;
    }

    /** Atalho de leitura em Blade: $model->estoque_formatado */
    public function getEstoqueFormatadoAttribute()
    {
        return $this->estoqueFormatado();
    }
}
