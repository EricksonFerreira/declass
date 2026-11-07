<?php

namespace App\Services;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EventoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Evento $evento)
    {
        $this->model = $evento;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Evento) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Evento');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Agenda do mes: eventos que se sobrepoem ao mes informado
     * (inicio <= fim do mes e fim efetivo >= inicio do mes),
     * ordenados por data de inicio.
     */
    public function agendaDoMes(\DateTimeInterface|string $mes, array $filtros = []): Collection
    {
        $filtros = $this->sanitizeEscolaContextFilter($filtros);

        $inicio = \Carbon\Carbon::parse($mes.'-01')->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

        return Evento::query()
            ->with('escola:id,nome', 'turmas:id,nome')
            ->whereDate('data_inicio', '<=', $fim->toDateString())
            ->whereRaw('COALESCE(data_fim, data_inicio) >= ?', [$inicio->toDateString()])
            ->when(! empty($filtros['escola_id']), fn ($q) => $q->where('escola_id', (int) $filtros['escola_id']))
            ->when(! empty($filtros['tipo']), fn ($q) => $q->where('tipo', $filtros['tipo']))
            ->when(! empty($filtros['status']), fn ($q) => $q->where('status', $filtros['status']))
            ->orderBy('data_inicio')
            ->orderBy('hora_inicio')
            ->get();
    }

    /**
     * Eventos visiveis no portal para os alunos informados:
     * publico "todos", nao cancelados, sobrepostos ao periodo
     * futuro, de escolas onde o aluno tem vinculo.
     */
    public function eventosParaAlunos(Collection $alunos): Collection
    {
        $escolaIds = $alunos->pluck('escola_id')->filter()->unique();

        if ($escolaIds->isEmpty()) {
            return collect();
        }

        // Eventos limitados a turmas especificas so aparecem se uma
        // delas for a turma (matricula ativa) de um dos alunos.
        $turmaIds = \App\Models\Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->where('status', 'ativa')
            ->pluck('turma_id')
            ->unique();

        return Evento::query()
            ->with('escola:id,nome')
            ->where('publico', 'todos')
            ->where('status', '!=', 'cancelado')
            ->whereRaw('COALESCE(data_fim, data_inicio) >= ?', [now()->toDateString()])
            ->where(fn ($q) => $q->whereNull('escola_id')->orWhereIn('escola_id', $escolaIds))
            ->where(fn ($q) => $q->whereDoesntHave('turmas')
                ->orWhereHas('turmas', fn ($t) => $t->whereIn('turmas.id', $turmaIds)))
            ->orderBy('data_inicio')
            ->orderBy('hora_inicio')
            ->limit(60)
            ->get();
    }
}
