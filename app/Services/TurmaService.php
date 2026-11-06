<?php

namespace App\Services;

use App\Models\Etapa;
use App\Models\Turma;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->with(['escola', 'anoLetivo', 'etapa']);

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
     * Replica as disciplinas ofertadas da turma de origem para as
     * turmas de destino: mesmas disciplinas e mesma carga horaria.
     * Disciplinas que o destino ja tem tem a carga atualizada —
     * nunca remove ofertas existentes do destino.
     *
     * @return array{disciplinas: int, turmas: int, puladas: int}
     */
    public function replicarDisciplinas(int $turmaOrigemId, array $turmaDestinoIds): array
    {
        $origem = $this->model->newQuery()->with('disciplinas')->findOrFail($turmaOrigemId);

        $destinos = $this->model->newQuery()
            ->whereIn('id', $turmaDestinoIds)
            ->where('id', '!=', $turmaOrigemId)
            ->get();

        $ofertas = $origem->disciplinas->mapWithKeys(
            fn ($d) => [$d->id => ['carga_horaria_semanal' => $d->pivot->carga_horaria_semanal]]
        )->all();

        $puladas = 0;
        foreach ($destinos as $destino) {
            foreach ($ofertas as $disciplinaId => $pivot) {
                if ($destino->disciplinas()->where('disciplinas.id', $disciplinaId)->exists()) {
                    $destino->disciplinas()->updateExistingPivot($disciplinaId, $pivot);
                    $puladas++;
                } else {
                    $destino->disciplinas()->attach($disciplinaId, $pivot);
                }
            }
        }

        return [
            'disciplinas' => count($ofertas),
            'turmas' => $destinos->count(),
            'puladas' => $puladas,
            'ano_letivo_id' => $origem->ano_letivo_id,
        ];
    }
}
