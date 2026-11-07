<?php

namespace App\Services;

use App\Models\Atribuicao;
use App\Models\Avaliacao;
use App\Models\Diario;
use App\Models\Etapa;
use App\Models\Turma;
use App\Models\User;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TurmaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Turma $turma)
    {
        $this->model = $turma;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Turma) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Turma');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Paginacao com filtros da sessao, eager loading das relacoes
     * de exibicao (escola, ano letivo, etapa).
     */
    public function paginateComFiltros(array $filters, int $perPage): LengthAwarePaginator
    {
        $filters = $this->sanitizeEscolaContextFilter($filters);

        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->with(['escola', 'anoLetivo', 'etapa']);

        // Professor ve apenas as turmas onde tem atribuicao ativa e,
        // por padrao, somente as do periodo letivo em andamento
        // (o filtro 'periodo_letivo' libera as demais).
        $user = auth()->user();
        if ($user?->isProfessor()) {
            $query->whereHas('atribuicoes', fn ($q) => $q
                ->where('user_id', $user->id)
                ->where('ativo', 'S'));

            $this->escopoPeriodoLetivo($query, (string) ($filters['periodo_letivo']['valor'] ?? 'atual'));
        }

        foreach ($filters as $field => $filter) {
            if (! is_array($filter) || ! array_key_exists('valor', $filter)) {
                continue;
            }

            $valor = $filter['valor'];
            if ($valor === null || $valor === '') {
                continue;
            }

            $typeWhere = $filter['typeWhere'] ?? 'where';

            if ($field === 'nome') {
                $query->where('nome', 'like', '%'.$valor.'%');

                continue;
            }

            if (! SchemaCache::hasColumn($this->model->getTable(), (string) $field)) {
                continue;
            }

            if ($typeWhere === 'like') {
                $query->where($field, 'like', '%'.$valor.'%');
            } elseif (is_array($valor)) {
                $query->whereIn($field, $valor);
            } else {
                $query->where($field, $valor);
            }
        }

        return $query->orderBy('nome')->orderBy('id')->paginate($perPage);
    }

    /** Professor so acessa turmas onde tem atribuicao ativa. */
    public function podeAcessar(Turma $turma, ?\App\Models\User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return false;
        }

        if (! $user->isProfessor()) {
            return true;
        }

        return $turma->atribuicoes()
            ->where('user_id', $user->id)
            ->where('ativo', 'S')
            ->exists();
    }

    /**
     * Filtra turmas pela situacao do periodo letivo do ano letivo:
     * 'atual' = ano com periodo ativo cobrindo hoje (ou ano 'letivo'
     * sem periodos configurados), 'fora' = o inverso, 'todas' = livre.
     */
    public function escopoPeriodoLetivo($query, string $modo): void
    {
        if ($modo === 'todas') {
            return;
        }

        $hoje = now()->toDateString();

        $emPeriodo = function ($q) use ($hoje) {
            $q->whereHas('anoLetivo', function ($a) use ($hoje) {
                $a->where('ativo', 'S')->where(function ($a2) use ($hoje) {
                    $a2->whereHas('periodos', fn ($p) => $p
                        ->where('ativo', 'S')
                        ->whereDate('data_inicio', '<=', $hoje)
                        ->whereDate('data_fim', '>=', $hoje))
                        ->orWhere(fn ($a3) => $a3
                            ->where('status', 'letivo')
                            ->whereDoesntHave('periodos'));
                });
            });
        };

        if ($modo === 'fora') {
            $query->whereNot($emPeriodo);
        } else {
            $query->where($emPeriodo);
        }
    }

    /** Ultimas aulas registradas no diario da turma (tela da turma). */
    public function diariosDaTurma(Turma $turma, int $limite = 15, ?User $viewer = null, array $filtros = []): Collection
    {
        return Diario::query()
            ->with(['disciplina', 'professor'])
            ->where('turma_id', $turma->id)
            ->where('empresa_id', $turma->empresa_id)
            ->when($viewer?->isProfessor(), fn ($q) => $q->where('diarios.user_id', $viewer->id))
            ->when($filtros['disciplina_id'] ?? null, fn ($q, $v) => $q->where('disciplina_id', $v))
            ->when($filtros['data_inicio'] ?? null, fn ($q, $v) => $q->whereDate('data', '>=', $v))
            ->when($filtros['data_fim'] ?? null, fn ($q, $v) => $q->whereDate('data', '<=', $v))
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->limit($limite)
            ->get();
    }

    /** Diario da turma na data de hoje (o mais recente, se houver varias disciplinas). */
    public function diarioDeHoje(Turma $turma, ?User $viewer = null): ?Diario
    {
        return Diario::query()
            ->where('empresa_id', $turma->empresa_id)
            ->where('turma_id', $turma->id)
            ->whereDate('data', now()->toDateString())
            ->when($viewer?->isProfessor(), fn ($q) => $q->where('diarios.user_id', $viewer->id))
            ->orderByDesc('id')
            ->first();
    }

    /** Avaliacoes da turma, mais recentes primeiro (tela da turma). */
    public function avaliacoesDaTurma(Turma $turma, int $limite = 15, ?User $viewer = null, array $filtros = []): Collection
    {
        return Avaliacao::query()
            ->with(['disciplina', 'professor'])
            ->where('turma_id', $turma->id)
            ->where('empresa_id', $turma->empresa_id)
            ->when($viewer?->isProfessor(), fn ($q) => $q->where('avaliacoes.user_id', $viewer->id))
            ->when($filtros['tipo'] ?? null, fn ($q, $v) => $q->where('tipo', $v))
            ->when($filtros['disciplina_id'] ?? null, fn ($q, $v) => $q->where('disciplina_id', $v))
            ->when($filtros['periodo_letivo_id'] ?? null, fn ($q, $v) => $q->where('periodo_letivo_id', $v))
            ->when($filtros['data_inicio'] ?? null, fn ($q, $v) => $q->whereDate('data', '>=', $v))
            ->when($filtros['data_fim'] ?? null, fn ($q, $v) => $q->whereDate('data', '<=', $v))
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->limit($limite)
            ->get();
    }

    /**
     * Diario e avaliacoes da turma numa lista unica ordenada por data.
     * Filtros compartilhados: disciplina_id, data_inicio, data_fim;
     * tipo e periodo_letivo_id valem apenas para avaliacoes.
     */
    public function registrosDaTurma(Turma $turma, int $limite = 30, ?User $viewer = null, array $filtros = []): \Illuminate\Support\Collection
    {
        $diarios = $this->diariosDaTurma($turma, $limite, $viewer, [
            'disciplina_id' => $filtros['disciplina_id'] ?? null,
            'data_inicio' => $filtros['data_inicio'] ?? null,
            'data_fim' => $filtros['data_fim'] ?? null,
        ]);

        $avaliacoes = $this->avaliacoesDaTurma($turma, $limite, $viewer, $filtros);

        $registros = collect();
        foreach ($diarios as $d) {
            $registros->push([
                'tipo' => 'diario',
                'data' => $d->data,
                'ordem' => $d->id,
                'item' => $d->conteudo,
                'disciplina' => $d->disciplina?->nome,
                'detalhe' => $d->professor?->nome,
                'model' => $d,
            ]);
        }
        foreach ($avaliacoes as $a) {
            $registros->push([
                'tipo' => 'avaliacao',
                'data' => $a->data,
                'ordem' => $a->id,
                'item' => $a->nome,
                'disciplina' => $a->disciplina?->nome,
                'detalhe' => (Avaliacao::TIPOS[$a->tipo] ?? $a->tipo).($a->periodo ? ' · '.$a->periodo : ''),
                'model' => $a,
            ]);
        }

        return $registros
            ->sortByDesc(fn ($r) => sprintf('%s-%012d', $r['data']?->toDateString() ?? '', $r['ordem']))
            ->values();
    }

    /**
     * Disciplinas da turma visiveis para o usuario: professor ve apenas
     * as que leciona (regente ve todas); demais perfis veem tudo.
     */
    public function disciplinasVisiveisNaTurma(Turma $turma, User $viewer): Collection
    {
        $ofertadas = $turma->disciplinas;
        if (! $viewer->isProfessor()) {
            return $ofertadas;
        }

        $minhas = $turma->atribuicoes->filter->estaAtiva()->where('user_id', $viewer->id);
        if ($minhas->contains(fn ($a) => $a->disciplina_id === null)) {
            return $ofertadas; // regente: todas as ofertadas
        }

        return $ofertadas->whereIn('id', $minhas->pluck('disciplina_id')->filter()->all())->values();
    }

    /**
     * Presencas consolidadas por matricula na turma:
     * [matricula_id => (presente, falta, justificada, total)].
     * $disciplinaId restringe a consolidacao as aulas daquela
     * disciplina (frequencia e sempre por disciplina).
     */
    public function presencasDaTurma(Turma $turma, ?User $viewer = null, ?int $disciplinaId = null): \Illuminate\Support\Collection
    {
        return DB::table('frequencias')
            ->join('diarios', 'frequencias.diario_id', '=', 'diarios.id')
            ->where('diarios.turma_id', $turma->id)
            ->where('frequencias.empresa_id', $turma->empresa_id)
            ->when($viewer?->isProfessor(), fn ($q) => $q->where('diarios.user_id', $viewer->id))
            ->when($disciplinaId, fn ($q) => $q->where('diarios.disciplina_id', $disciplinaId))
            ->whereNull('diarios.deleted_at')
            ->whereNull('frequencias.deleted_at')
            ->groupBy('frequencias.matricula_id')
            ->selectRaw("
                frequencias.matricula_id,
                SUM(CASE WHEN frequencias.status = 'presente' THEN 1 ELSE 0 END) as presente,
                SUM(CASE WHEN frequencias.status = 'justificada' THEN 1 ELSE 0 END) as justificada,
                SUM(CASE WHEN frequencias.status = 'abonada' THEN 1 ELSE 0 END) as abonada,
                SUM(CASE WHEN frequencias.status = 'falta' THEN 1 ELSE 0 END) as falta,
                SUM(CASE WHEN frequencias.status <> 'abonada' THEN 1 ELSE 0 END) as total
            ")
            ->get()
            ->keyBy('matricula_id');
    }

    /**
     * Gera turmas em lote: etapas x sufixos x turnos.
     * Nomes existentes no mesmo ano letivo sao pulados (nunca
     * duplica nem sobrescreve). Quando mais de um turno e
     * selecionado, o nome recebe o sufixo do turno para evitar
     * colisao ("1º Ano A — Matutino").
     *
     * @return array{criadas: int, puladas: array<int, string>}
     */
    public function gerarLote(array $data): array
    {
        $anoLetivoId = (int) $data['ano_letivo_id'];
        $etapas = Etapa::query()->whereIn('id', $data['etapas'])->get()->keyBy('id');
        $sufixos = $data['sufixos'];
        $turnos = $data['turnos'] ?? [];
        $rotuloTurno = count($turnos) > 1;

        $existentes = $this->model->newQuery()
            ->where('ano_letivo_id', $anoLetivoId)
            ->pluck('nome')
            ->map(fn ($n) => mb_strtolower((string) $n))
            ->all();

        $criadas = 0;
        $puladas = [];

        foreach ($etapas as $etapa) {
            foreach ($sufixos as $sufixo) {
                foreach ($turnos === [] ? [null] : $turnos as $turno) {
                    $nome = trim($etapa->nome.' '.$sufixo.($rotuloTurno && $turno ? ' — '.(Turma::TURNOS[$turno] ?? $turno) : ''));

                    if (in_array(mb_strtolower($nome), $existentes, true)) {
                        $puladas[] = $nome;

                        continue;
                    }

                    $this->create([
                        'escola_id' => $data['escola_id'],
                        'ano_letivo_id' => $anoLetivoId,
                        'etapa_id' => $etapa->id,
                        'nome' => $nome,
                        'turno' => $turno,
                        'sala' => $data['sala'] ?? null,
                        'capacidade_maxima' => $data['capacidade_maxima'] ?? null,
                        'ativo' => $data['ativo'] ?? 'S',
                    ]);

                    $existentes[] = mb_strtolower($nome);
                    $criadas++;
                }
            }
        }

        return ['criadas' => $criadas, 'puladas' => $puladas];
    }

    /**
     * Replica a organizacao da turma de origem para as turmas de
     * destino: mesmas disciplinas ofertadas (com carga horaria) e,
     * opcionalmente, os mesmos professores atribuidos.
     *
     * Disciplinas que o destino ja tem tem a carga atualizada —
     * nunca remove ofertas existentes. Atribuicoes repetidas
     * (mesmo professor + mesma disciplina) tem papel/status
     * atualizados; atribuicoes do destino nunca sao removidas.
     *
     * @return array{disciplinas: int, turmas: int, cargas_atualizadas: int,
     *     professores_criados: int, professores_atualizados: int, ano_letivo_id: int}
     */
    public function replicarOrganizacao(int $turmaOrigemId, array $turmaDestinoIds, bool $incluirProfessores = true): array
    {
        $origem = $this->model->newQuery()
            ->with(['disciplinas', 'atribuicoes.user', 'atribuicoes.disciplina'])
            ->findOrFail($turmaOrigemId);

        $destinos = $this->model->newQuery()
            ->whereIn('id', $turmaDestinoIds)
            ->where('id', '!=', $turmaOrigemId)
            ->get();

        $ofertas = $origem->disciplinas->mapWithKeys(
            fn ($d) => [$d->id => ['carga_horaria_semanal' => $d->pivot->carga_horaria_semanal]]
        )->all();

        $cargasAtualizadas = 0;
        foreach ($destinos as $destino) {
            foreach ($ofertas as $disciplinaId => $pivot) {
                if ($destino->disciplinas()->where('disciplinas.id', $disciplinaId)->exists()) {
                    $destino->disciplinas()->updateExistingPivot($disciplinaId, $pivot);
                    $cargasAtualizadas++;
                } else {
                    $destino->disciplinas()->attach($disciplinaId, $pivot);
                }
            }
        }

        $professoresCriados = 0;
        $professoresAtualizados = 0;

        if ($incluirProfessores) {
            $atribuicoesOrigem = $origem->atribuicoes->filter->estaAtiva()->values();

            foreach ($destinos as $destino) {
                $existentes = $destino->atribuicoes()->get()
                    ->keyBy(fn (Atribuicao $a) => $a->user_id.'|'.($a->disciplina_id ?? 0));

                foreach ($atribuicoesOrigem as $atribuicao) {
                    $chave = $atribuicao->user_id.'|'.($atribuicao->disciplina_id ?? 0);

                    if (isset($existentes[$chave])) {
                        $existentes[$chave]->update([
                            'papel' => $atribuicao->papel,
                            'ativo' => $atribuicao->ativo,
                        ]);
                        $professoresAtualizados++;
                    } else {
                        Atribuicao::create([
                            'empresa_id' => $destino->empresa_id,
                            'turma_id' => $destino->id,
                            'disciplina_id' => $atribuicao->disciplina_id,
                            'user_id' => $atribuicao->user_id,
                            'papel' => $atribuicao->papel,
                            'ativo' => $atribuicao->ativo,
                        ]);
                        $professoresCriados++;
                    }
                }
            }
        }

        return [
            'disciplinas' => count($ofertas),
            'turmas' => $destinos->count(),
            'cargas_atualizadas' => $cargasAtualizadas,
            'professores_criados' => $professoresCriados,
            'professores_atualizados' => $professoresAtualizados,
            'ano_letivo_id' => $origem->ano_letivo_id,
        ];
    }
}
