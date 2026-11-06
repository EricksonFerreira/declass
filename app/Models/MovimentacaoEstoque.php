<?php

namespace App\Models;

use App\Models\Concerns\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimentacaoEstoque extends Model
{
    use HasAuditColumns;

    protected $table = 'movimentacoes_estoque';

    protected $fillable = [
        'empresa_id',
        'escola_id',
        'material_id',
        'tipo',
        'quantidade',
        'saldo_anterior',
        'saldo_novo',
        'motivo',
        'data',
        'user_id',
    ];

    protected $casts = [
        'data' => 'date',
        'quantidade' => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_novo' => 'decimal:2',
    ];

    public const TIPOS = [
        'entrada' => 'Entrada',
        'saida' => 'Saída',
        'ajuste' => 'Ajuste de saldo',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    public function quantidadeFormatada(): string
    {
        $qtd = rtrim(rtrim(number_format((float) $this->quantidade, 2, ',', ''), '0'), ',');

        return $qtd;
    }
}
