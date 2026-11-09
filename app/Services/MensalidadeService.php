<?php

namespace App\Services;

use App\Models\Matricula;
use App\Models\Mensalidade;
use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
     * Lista mensalidades com filtros de sessao: aluno ('nome' busca
     * em pessoa via matricula), turma, competencia e status
     * (inclui 'vencida' derivada).
     */
    public function listarComFiltros(array $filters, int $perPage = 15)
    {
        return $this->queryComFiltros($filters)->paginate($perPage);
    }

    /** Totais em R$ do escopo filtrado (independente da paginacao). */
    public function resumoIndex(array $filters): array
    {
        $base = $this->queryComFiltros($filters)
            ->reorder()
            ->select(\Illuminate\Support\Facades\DB::raw("
                COUNT(*) as cobrancas,
                COALESCE(SUM(valor),0) as total,
                SUM(CASE WHEN status = 'paga' THEN valor ELSE 0 END) as recebido,
                SUM(CASE WHEN status = 'pendente' AND vencimento < CURDATE() THEN valor ELSE 0 END) as vencido,
                SUM(CASE WHEN status = 'pendente' AND vencimento >= CURDATE() THEN valor ELSE 0 END) as a_receber
            "))
            ->first();

        return [
            'cobrancas' => (int) ($base->cobrancas ?? 0),
            'total' => (float) ($base->total ?? 0),
            'recebido' => (float) ($base->recebido ?? 0),
            'vencido' => (float) ($base->vencido ?? 0),
            'a_receber' => (float) ($base->a_receber ?? 0),
        ];
    }

    private function queryComFiltros(array $filters)
    {
        $nome = $this->filtroValor($filters, 'nome');
        $busca = $this->filtroValor($filters, 'busca') ?? $nome;
        $alunoId = $this->filtroValor($filters, 'aluno_id');
        $turmaId = $this->filtroValor($filters, 'turma_id');
        $competencia = $this->filtroValor($filters, 'competencia');
        $status = $this->filtroValor($filters, 'status');

        $query = Mensalidade::query()
            ->with(['matricula.aluno.pessoa', 'matricula.turma', 'planoMensalidade'])
            ->orderBy('competencia', 'desc')
            ->orderBy('vencimento', 'asc');

        if ($alunoId) {
            $query->whereHas('matricula', fn ($q) => $q->where('aluno_id', (int) $alunoId));
        }

        // Busca "estilo cartorio": cada palavra digitada precisa
        // bater no nome do aluno ou no RA, em qualquer ordem.
        if ($busca) {
            $tokens = collect(preg_split('/\s+/u', trim($busca)))
                ->filter(fn ($t) => mb_strlen($t) > 0)->unique()->take(6);
            foreach ($tokens as $token) {
                $query->whereHas('matricula.aluno', fn ($q) => $q
                    ->where('registro_academico', 'like', '%'.$token.'%')
                    ->orWhereHas('pessoa', fn ($p) => $p->where('nome', 'like', '%'.$token.'%')));
            }
        }

        if ($turmaId) {
            $query->whereHas('matricula', fn ($q) => $q->where('turma_id', (int) $turmaId));
        }

        if ($competencia) {
            $query->where('competencia', $competencia);
        }

        if ($status === 'vencida') {
            $query->where('status', 'pendente')->whereDate('vencimento', '<', now()->toDateString());
        } elseif ($status) {
            $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Relatorio de inadimplencia: uma linha por mensalidade com
     * dados do aluno/escola/turma e situacao derivada
     * (vencida = pendente com vencimento passado). Ordenado pelas
     * dividas mais antigas primeiro.
     * Filtros de sessao: nome (aluno), escola_id, turma_id,
     * competencia e situacao.
     */
    public function relatorioInadimplencia(array $filtros = [], int $perPage = 25)
    {
        return $this->queryRelatorio($filtros)
            ->paginate($perPage);
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

        $filtros = $this->sanitizeEscolaContextFilter($filtros);

        if ($nome = $this->filtroValor($filtros, 'nome')) {
            $query->where('pessoas.nome', 'like', '%'.$nome.'%');
        }
        if ($escolaId = $this->filtroValor($filtros, 'escola_id')) {
            $query->where('turmas.escola_id', (int) $escolaId);
        }
        if ($turmaId = $this->filtroValor($filtros, 'turma_id')) {
            $query->where('matriculas.turma_id', (int) $turmaId);
        }
        if ($competencia = $this->filtroValor($filtros, 'competencia')) {
            $query->where('mensalidades.competencia', $competencia);
        }
        if ($situacao = $this->filtroValor($filtros, 'situacao')) {
            match ($situacao) {
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

                $valorPlano = (float) ($matricula->planoMensalidade?->valor ?? 0);
                $desconto = (float) ($matricula->bolsa_percentual ?? 0);
                $valor = $desconto > 0
                    ? round($valorPlano * (1 - min($desconto, 100) / 100), 2)
                    : $valorPlano;

                Mensalidade::create([
                    'matricula_id' => $matricula->id,
                    'plano_mensalidade_id' => $matricula->plano_mensalidade_id,
                    'competencia' => $competencia,
                    'valor' => $valor,
                    'vencimento' => $vencimento,
                    'status' => 'pendente',
                ]);
                $criadas++;
            }

            return ['criadas' => $criadas, 'ignoradas' => $ignoradas];
        });
    }

    /** Anexa/substitui o boleto (PDF) da mensalidade no disco public. */
    public function salvarBoleto(Mensalidade $mensalidade, UploadedFile $arquivo): void
    {
        $this->removerBoleto($mensalidade);

        $mensalidade->update([
            'boleto_path' => UploadStorage::putFile(
                $arquivo,
                "empresas/{$mensalidade->empresa_id}/boletos/{$mensalidade->id}",
                null,
                'public'
            ),
            'boleto_nome' => $arquivo->getClientOriginalName(),
        ]);
    }

    /** Remove o boleto anexado (arquivo e referencia). */
    public function removerBoleto(Mensalidade $mensalidade): void
    {
        if (! $mensalidade->boleto_path) {
            return;
        }

        Storage::disk('public')->delete($mensalidade->boleto_path);
        $mensalidade->update(['boleto_path' => null, 'boleto_nome' => null]);
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
