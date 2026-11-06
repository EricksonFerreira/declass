<?php

namespace App\Services;

use App\Models\Matricula;
use App\Models\Mensalidade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MensalidadeService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Mensalidade $mensalidade)
    {
        $this->model = $mensalidade;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Mensalidade) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Mensalidade');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Lista mensalidades com filtros: aluno, turma, competencia,
     * status (inclui 'vencida' derivada) e situacao financeira.
     */
    public function listarComFiltros(array $filters, int $perPage = 15)
    {
        $query = Mensalidade::query()
            ->with(['matricula.aluno.pessoa', 'matricula.turma', 'planoMensalidade'])
            ->orderBy('competencia', 'desc')
            ->orderBy('vencimento', 'asc');

        if (! empty($filters['aluno_id'])) {
            $query->whereHas('matricula', fn ($q) => $q->where('aluno_id', $filters['aluno_id']));
        }

        if (! empty($filters['turma_id'])) {
            $query->whereHas('matricula', fn ($q) => $q->where('turma_id', $filters['turma_id']));
        }

        if (! empty($filters['competencia'])) {
            $query->where('competencia', $filters['competencia']);
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'vencida') {
                $query->where('status', 'pendente')->whereDate('vencimento', '<', now()->toDateString());
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (! empty($filters['inadimplente']) && $filters['inadimplente'] === 'S') {
            $query->where('status', 'pendente')->whereDate('vencimento', '<', now()->toDateString());
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Gera mensalidades para todas as matriculas ativas com plano
     * definido, na competencia informada. Ignora competencias ja
     * existentes (unique matricula+competencia).
     *
     * @return array{criadas: int, ignoradas: int}
     */
    public function gerarCompetencia(string $competencia, string $vencimento): array
    {
        return DB::transaction(function () use ($competencia, $vencimento) {
            $matriculas = Matricula::query()
                ->where('status', 'ativa')
                ->whereNotNull('plano_mensalidade_id')
                ->with('planoMensalidade')
                ->get();

            $criadas = 0;
            $ignoradas = 0;

            foreach ($matriculas as $matricula) {
                $existe = Mensalidade::query()
                    ->where('matricula_id', $matricula->id)
                    ->where('competencia', $competencia)
                    ->exists();

                if ($existe) {
                    $ignoradas++;

                    continue;
                }

                Mensalidade::create([
                    'matricula_id' => $matricula->id,
                    'plano_mensalidade_id' => $matricula->plano_mensalidade_id,
                    'competencia' => $competencia,
                    'valor' => $matricula->planoMensalidade?->valor ?? 0,
                    'vencimento' => $vencimento,
                    'status' => 'pendente',
                ]);
                $criadas++;
            }

            return ['criadas' => $criadas, 'ignoradas' => $ignoradas];
        });
    }

    /** Baixa (recebimento) de uma mensalidade pendente. */
    public function baixar(Mensalidade $mensalidade, ?string $formaPagamento = null, ?string $dataPagamento = null): void
    {
        if ($mensalidade->status !== 'pendente') {
            throw new \DomainException('Apenas mensalidades pendentes podem ser baixadas.');
        }

        $mensalidade->update([
            'status' => 'paga',
            'data_pagamento' => $dataPagamento ?? now()->toDateString(),
            'forma_pagamento' => $formaPagamento,
        ]);
    }

    /** Alunos com mensalidades em aberto/vencidas (inadimplencia). */
    public function inadimplentes(): Collection
    {
        return Mensalidade::query()
            ->where('status', 'pendente')
            ->whereDate('vencimento', '<', now()->toDateString())
            ->with(['matricula.aluno.pessoa', 'matricula.turma'])
            ->orderBy('vencimento')
            ->get()
            ->groupBy(fn (Mensalidade $m) => $m->matricula->aluno_id);
    }
}
