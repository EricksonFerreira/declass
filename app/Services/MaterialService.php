<?php

namespace App\Services;

use App\Models\Material;
use App\Models\MovimentacaoEstoque;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaterialService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Material $material)
    {
        $this->model = $material;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Material) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Material');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Registra movimentacao e atualiza o saldo do material
     * em transacao. Regras:
     *  - entrada: saldo += quantidade
     *  - saida:   saldo -= quantidade (nao pode ficar negativo)
     *  - ajuste:  quantidade e o novo saldo absoluto (>= 0)
     */
    public function movimentar(Material $material, array $data): MovimentacaoEstoque
    {
        return DB::transaction(function () use ($material, $data) {
            $material = Material::query()->lockForUpdate()->findOrFail($material->id);

            $anterior = (float) $material->estoque_atual;
            $quantidade = (float) $data['quantidade'];
            $tipo = $data['tipo'];

            $novo = match ($tipo) {
                'entrada' => $anterior + $quantidade,
                'saida' => $anterior - $quantidade,
                'ajuste' => $quantidade,
                default => throw ValidationException::withMessages([
                    'tipo' => 'Tipo de movimentação inválido.',
                ]),
            };

            if ($novo < 0) {
                throw ValidationException::withMessages([
                    'quantidade' => 'Estoque insuficiente: saldo atual é '.number_format($anterior, 2, ',', '.').' e a saída seria de '.number_format($quantidade, 2, ',', '.').'.',
                ]);
            }

            $movimentacao = MovimentacaoEstoque::create([
                'empresa_id' => $material->empresa_id,
                'material_id' => $material->id,
                'tipo' => $tipo,
                'quantidade' => $quantidade,
                'saldo_anterior' => $anterior,
                'saldo_novo' => $novo,
                'motivo' => $data['motivo'] ?? null,
                'data' => $data['data'] ?? now()->toDateString(),
                'user_id' => auth()->id(),
            ]);

            $material->update(['estoque_atual' => $novo]);

            return $movimentacao;
        });
    }

    /** Movimentacoes do material, mais recentes primeiro. */
    public function movimentacoesDoMaterial(int $materialId, int $perPage = 20)
    {
        return MovimentacaoEstoque::query()
            ->where('material_id', $materialId)
            ->with('usuario:id,name')
            ->orderBy('data', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Quantidade de movimentacoes registradas para o material. */
    public function totalMovimentacoes(int $materialId): int
    {
        return MovimentacaoEstoque::query()->where('material_id', $materialId)->count();
    }
}
