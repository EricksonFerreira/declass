<?php

namespace App\Services;

use App\Models\Evento;
use App\Models\Feriado;
use App\Models\HorarioSlot;
use App\Models\HorarioTurma;
use App\Models\Turma;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Calendario letivo de uma turma: cruza os dias letivos da
 * grade vinculada com feriados e eventos para responder, dia a
 * dia, se ha aula normal, so evento, evento+aula ou nada.
 */
class CalendarioLetivoService
{
    public const STATUS_NAO_LETIVO = 'nao_letivo';   // dia sem slots (fds/nao letivo)

    public const STATUS_AULA = 'aula';               // aula normal

    public const STATUS_SEM_AULA = 'sem_aula';       // feriado/evento que suspende aulas

    public const STATUS_EVENTO = 'evento';           // so evento, sem aula letiva no dia

    public const STATUS_AULA_EVENTO = 'aula_evento'; // aula normal + evento convivendo

    public const ROTULOS = [
        self::STATUS_NAO_LETIVO => 'Sem aula',
        self::STATUS_AULA => 'Aula normal',
        self::STATUS_SEM_AULA => 'Suspenso',
        self::STATUS_EVENTO => 'Evento',
        self::STATUS_AULA_EVENTO => 'Aula + evento',
    ];

    /**
     * Dias da semana letivos da turma vindos do horario vinculado
     * (ou da matriz legada por escola+turno; fallback seg-sex).
     */
    public function diasLetivosDaTurma(Turma $turma): array
    {
        $q = $this->slotsQueryDaTurma($turma);
        if (! $q) {
            return [1, 2, 3, 4, 5];
        }

        $dias = $q->distinct()->pluck('dia_semana')->all();

        return $dias ?: [1, 2, 3, 4, 5];
    }

    /**
     * Aulas que o professor leciona na turma, agrupadas por dia da
     * semana (1=seg..7=dom), com horario do slot — usado para
     * destacar no calendario os dias de trabalho dele.
     */
    public function aulasDoProfessor(Turma $turma, int $userId): Collection
    {
        $slots = ($this->slotsQueryDaTurma($turma) ?? HorarioSlot::query()->whereRaw('1 = 0'))
            ->get()
            ->keyBy(fn ($s) => $s->dia_semana.'-'.$s->ordem);

        return HorarioTurma::query()
            ->where('turma_id', $turma->id)
            ->where('user_id', $userId)
            ->with('disciplina:id,nome,sigla')
            ->orderBy('ordem')
            ->get()
            ->groupBy('dia_semana')
            ->map(fn ($aulas) => $aulas->map(function (HorarioTurma $aula) use ($slots) {
                $slot = $slots[$aula->dia_semana.'-'.$aula->ordem] ?? null;

                return [
                    'disciplina' => $aula->disciplina?->sigla ?: ($aula->disciplina?->nome ?? '—'),
                    'inicio' => $slot ? substr((string) $slot->hora_inicio, 0, 5) : null,
                    'fim' => $slot ? substr((string) $slot->hora_fim, 0, 5) : null,
                ];
            }));
    }

    /** Slots do horario vinculado a turma (ou matriz legada por escola+turno). */
    private function slotsQueryDaTurma(Turma $turma)
    {
        if ($turma->horario_id) {
            return HorarioSlot::query()->where('horario_id', $turma->horario_id);
        }

        if ($turma->escola_id && $turma->turno) {
            return HorarioSlot::query()
                ->where('escola_id', $turma->escola_id)
                ->where('turno', $turma->turno);
        }

        return null;
    }

    /**
     * Calendario dia a dia no periodo. Retorna Collection indexada
     * por 'Y-m-d' => [status, letivo, feriados, eventos].
     * $somenteEventosPublicos=true filtra para visao de familia.
     */
    public function calendarioDaTurma(
        Turma $turma,
        Carbon $inicio,
        Carbon $fim,
        bool $somenteEventosPublicos = false
    ): Collection {
        $diasLetivos = array_flip($this->diasLetivosDaTurma($turma));
        $feriados = $this->feriadosNoPeriodo($turma, $inicio, $fim);
        $eventos = $this->eventosNoPeriodo($turma, $inicio, $fim, $somenteEventosPublicos);

        // Indexa ocorrencias por data para lookup O(1) por dia.
        $feriadosPorDia = $this->ocorrenciasPorDia($feriados, $inicio, $fim);
        $eventosPorDia = $this->ocorrenciasPorDia($eventos, $inicio, $fim);

        $dias = collect();
        for ($dia = $inicio->copy(); $dia->lte($fim); $dia->addDay()) {
            $chave = $dia->toDateString();
            $letivo = isset($diasLetivos[$dia->dayOfWeekIso]);

            $feriadosDia = $feriadosPorDia[$chave] ?? collect();
            $eventosDia = $eventosPorDia[$chave] ?? collect();

            $suspenso = $feriadosDia->contains('suspende_aulas', 'S')
                || $eventosDia->contains(fn ($e) => $this->eventoSuspendePara($e, $turma));

            $status = match (true) {
                ! $letivo && $eventosDia->isNotEmpty() => self::STATUS_EVENTO,
                ! $letivo => self::STATUS_NAO_LETIVO,
                $suspenso => self::STATUS_SEM_AULA,
                $eventosDia->isNotEmpty() => self::STATUS_AULA_EVENTO,
                default => self::STATUS_AULA,
            };

            $dias->put($chave, [
                'data' => $dia->copy(),
                'letivo' => $letivo,
                'status' => $status,
                'feriados' => $feriadosDia,
                'eventos' => $eventosDia,
            ]);
        }

        return $dias;
    }

    /**
     * Agrupa o calendario por mes para renderizacao:
     * [['rotulo' => 'Novembro/2026', 'semanas' => [[celulas]]], ...].
     */
    public function mesesDoCalendario(Collection $dias): array
    {
        $meses = [];
        foreach ($dias->groupBy(fn ($d) => $d['data']->format('Y-m')) as $chaveMes => $diasMes) {
            $primeiro = $diasMes->first()['data']->copy()->startOfMonth();
            $semanas = [];
            $semana = array_fill(1, 7, null);

            foreach ($diasMes as $d) {
                $semana[$d['data']->dayOfWeekIso] = $d;
                if ($d['data']->dayOfWeekIso === 7) {
                    $semanas[] = $semana;
                    $semana = array_fill(1, 7, null);
                }
            }
            if (array_filter($semana)) {
                $semanas[] = $semana;
            }

            $meses[] = [
                'chave' => $chaveMes,
                'rotulo' => ucfirst($primeiro->locale('pt_BR')->translatedFormat('F/Y')),
                'semanas' => $semanas,
            ];
        }

        return $meses;
    }

    /** Feriados que se sobrepoem ao periodo e atingem a escola da turma. */
    public function feriadosNoPeriodo(Turma $turma, Carbon $inicio, Carbon $fim): Collection
    {
        return Feriado::query()
            ->whereDate('data_inicio', '<=', $fim->toDateString())
            ->whereRaw('COALESCE(data_fim, data_inicio) >= ?', [$inicio->toDateString()])
            ->where(fn ($q) => $q->whereNull('escola_id')->orWhere('escola_id', $turma->escola_id))
            ->orderBy('data_inicio')
            ->get();
    }

    /**
     * Eventos nao cancelados que se sobrepoem ao periodo e atingem a
     * turma: escola nula (rede) ou a escola da turma, e sem restricao
     * de turmas especificas ou com a turma entre elas.
     */
    public function eventosNoPeriodo(
        Turma $turma,
        Carbon $inicio,
        Carbon $fim,
        bool $somentePublicos = false
    ): Collection {
        return Evento::query()
            ->with('turmas:id')
            ->where('status', '!=', 'cancelado')
            ->when($somentePublicos, fn ($q) => $q->where('publico', 'todos'))
            ->whereDate('data_inicio', '<=', $fim->toDateString())
            ->whereRaw('COALESCE(data_fim, data_inicio) >= ?', [$inicio->toDateString()])
            ->where(fn ($q) => $q->whereNull('escola_id')->orWhere('escola_id', $turma->escola_id))
            ->where(fn ($q) => $q->whereDoesntHave('turmas')
                ->orWhereHas('turmas', fn ($t) => $t->where('turmas.id', $turma->id)))
            ->orderBy('data_inicio')
            ->orderBy('hora_inicio')
            ->get();
    }

    /**
     * Resumo dos proximos dias com suspensao/ eventos para exibir
     * como aviso ("nao ha aula dia X").
     */
    public function proximasAlteracoes(Collection $dias, int $limite = 10): Collection
    {
        return $dias->filter(fn ($d) => $d['data']->isToday() || $d['data']->isFuture())
            ->filter(fn ($d) => in_array($d['status'], [self::STATUS_SEM_AULA, self::STATUS_EVENTO, self::STATUS_AULA_EVENTO]))
            ->take($limite)
            ->values();
    }

    private function eventoSuspendePara(Evento $evento, Turma $turma): bool
    {
        return $evento->suspendeAulasPara($turma->id);
    }

    /** Indexa cada ocorrencia (com data_inicio/data_fim) em cada dia coberto. */
    private function ocorrenciasPorDia(Collection $itens, Carbon $inicio, Carbon $fim): array
    {
        $porDia = [];
        foreach ($itens as $item) {
            $ini = Carbon::parse($item->data_inicio)->max($inicio->copy());
            $fimItem = Carbon::parse($item->data_fim ?? $item->data_inicio)->min($fim->copy());
            for ($d = $ini->copy(); $d->lte($fimItem); $d->addDay()) {
                $porDia[$d->toDateString()] ??= collect();
                $porDia[$d->toDateString()]->push($item);
            }
        }

        return $porDia;
    }
}
