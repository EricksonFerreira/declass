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
     * Relatorio de inadimplencia: uma linha por mensalidade com
     * dados do aluno/escola/turma e situacao derivada
     * (vencida = pendente com vencimento passado). Ordenado pelas
     * dividas mais antigas primeiro.
     * Filtros: escola_id, turma_id, competencia, situacao.
     */
    public function relatorioInadimplencia(array $filtros = [], int $perPage = 25)
    {
        return $this->queryRelatorio($filtros)
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Totais do relatorio por situacao (independentes da paginacao). */
    public function resumoInadimplencia(array $filtros = []): array
    {
        $hoje = now()->toDateString();
        $base = $this->queryRelatorio($filtros, false)
            ->select(DB::raw("
                COUNT(*) as total,
                SUM(CASE WHEN mensalidades.status = 'paga' THEN 1 ELSE 0 END) as pagas,
                SUM(CASE WHEN mensalidades.status = 'cancelada' THEN 1 ELSE 0 END) as canceladas,
                SUM(CASE WHEN mensalidades.status = 'pendente' AND mensalidades.vencimento < '{$hoje}' THEN 1 ELSE 0 END) as vencidas,
                SUM(CASE WHEN mensalidades.status = 'pendente' AND mensalidades.vencimento >= '{$hoje}' THEN 1 ELSE 0 END) as a_vencer,
                SUM(CASE WHEN mensalidades.status = 'pendente' THEN mensalidades.valor ELSE 0 END) as valor_aberto,
                SUM(CASE WHEN mensalidades.status = 'pendente' AND mensalidades.vencimento < '{$hoje}' THEN mensalidades.valor ELSE 0 END) as valor_vencido,
                SUM(CASE WHEN mensalidades.status = 'paga' THEN mensalidades.valor ELSE 0 END) as valor_pago
            "))
            ->first();

        return [
            'total' => (int) ($base->total ?? 0),
            'pagas' => (int) ($base->pagas ?? 0),
            'canceladas' => (int) ($base->canceladas ?? 0),
            'vencidas' => (int) ($base->vencidas ?? 0),
            'a_vencer' => (int) ($base->a_vencer ?? 0),
            'valor_aberto' => (float) ($base->valor_aberto ?? 0),
            'valor_vencido' => (float) ($base->valor_vencido ?? 0),
            'valor_pago' => (float) ($base->valor_pago ?? 0),
        ];
    }

    private function queryRelatorio(array $filtros, bool $comSelect = true)
    {
        $hoje = now()->toDateString();

        $query = Mensalidade::query()
            ->join('matriculas', 'mensalidades.matricula_id', '=', 'matriculas.id')
            ->join('alunos', 'matriculas.aluno_id', '=', 'alunos.id')
            ->join('pessoas', 'alunos.pessoa_id', '=', 'pessoas.id')
            ->join('turmas', 'matriculas.turma_id', '=', 'turmas.id')
            ->join('escolas', 'turmas.escola_id', '=', 'escolas.id')
            ->whereNull('matriculas.deleted_at')
            ->whereNull('alunos.deleted_at')
            ->whereNull('turmas.deleted_at');

        if ($comSelect) {
            $query->select([
                'mensalidades.id',
                'mensalidades.competencia',
                'mensalidades.vencimento',
                'mensalidades.valor',
                'mensalidades.status',
                'mensalidades.data_pagamento',
                'mensalidades.forma_pagamento',
                'pessoas.nome as aluno',
                'turmas.nome as turma',
                'escolas.nome as escola',
                'matriculas.numero_chamada',
                DB::raw("CASE
                    WHEN mensalidades.status = 'paga' THEN 'paga'
                    WHEN mensalidades.status = 'cancelada' THEN 'cancelada'
                    WHEN mensalidades.vencimento < '{$hoje}' THEN 'vencida'
                    ELSE 'pendente'
                END as situacao"),
            ])
                ->orderByRaw("CASE WHEN mensalidades.status = 'pendente' THEN 0 ELSE 1 END")
                ->orderBy('mensalidades.vencimento')
                ->orderBy('pessoas.nome');
        }

        // Escopo de tenant na coluna qualificada (joins tornam 'empresa_id' ambiguo)
        $user = auth()->user();
        $empresaId = $user?->isSuperAdmin()
            ? app(\App\Support\EmpresaContext::class)->empresaId()
            : $user?->empresa_id;
        if ($empresaId !== null) {
            $query->where('mensalidades.empresa_id', (int) $empresaId);
        }

        if (! empty($filtros['escola_id'])) {
            $query->where('turmas.escola_id', (int) $filtros['escola_id']);
        }
        if (! empty($filtros['turma_id'])) {
            $query->where('matriculas.turma_id', (int) $filtros['turma_id']);
        }
        if (! empty($filtros['competencia'])) {
            $query->where('mensalidades.competencia', $filtros['competencia']);
        }
        if (! empty($filtros['situacao'])) {
            match ($filtros['situacao']) {
                'vencida' => $query->where('mensalidades.status', 'pendente')->whereDate('mensalidades.vencimento', '<', $hoje),
                'a_vencer' => $query->where('mensalidades.status', 'pendente')->whereDate('mensalidades.vencimento', '>=', $hoje),
                'paga' => $query->where('mensalidades.status', 'paga'),
                'cancelada' => $query->where('mensalidades.status', 'cancelada'),
                default => null,
            };
        }

        return $query;
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
