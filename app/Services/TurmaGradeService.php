<?php

namespace App\Services;

use App\Models\HorarioSlot;
use App\Models\HorarioTurma;
use App\Models\Turma;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Grade horaria da turma: posiciona as disciplinas ofertadas nos
 * slots da matriz de horarios (escola + turno), controlando carga
 * horaria restante e conflitos de professor/sala.
 */
class TurmaGradeService
{
    /** Paleta deterministica para colorir disciplinas na grade. */
    public const CORES = [
        ['bg' => '#dbeafe', 'tx' => '#1e40af'], // azul
        ['bg' => '#dcfce7', 'tx' => '#166534'], // verde
        ['bg' => '#fef3c7', 'tx' => '#92400e'], // ambar
        ['bg' => '#fce7f3', 'tx' => '#9d174d'], // rosa
        ['bg' => '#ede9fe', 'tx' => '#5b21b6'], // violeta
        ['bg' => '#cffafe', 'tx' => '#155e75'], // ciano
        ['bg' => '#fee2e2', 'tx' => '#991b1b'], // vermelho
        ['bg' => '#ffedd5', 'tx' => '#9a3412'], // laranja
    ];

    public static function corDaDisciplina(int|string $chave): array
    {
        return self::CORES[abs(crc32((string) $chave)) % count(self::CORES)];
    }

    /**
     * Grade semanal do professor: as celulas de aula (horarios_turma)
     * atribuidas a ele nas turmas onde tem atribuicao ativa, com os
     * horarios de cada turma vindos da sua matriz vinculada.
     *
     * Retorna [dias, ordens, celulas, turmas] — celulas indexadas por
     * "dia-ordem" com a lista de aulas do professor naquele slot.
     */
    public function gradeSemanalDoProfessor(
        \App\Models\User $professor,
        ?int $anoLetivoId = null,
        ?int $escolaId = null,
        ?string $turno = null
    ): array {
        $turmas = Turma::query()
            ->where('ativo', 'S')
            ->whereHas('atribuicoes', fn ($q) => $q
                ->where('user_id', $professor->id)->where('ativo', 'S'))
            ->when($anoLetivoId, fn ($q) => $q->where('ano_letivo_id', $anoLetivoId))
            ->when($escolaId, fn ($q) => $q->where('escola_id', $escolaId))
            ->when($turno, fn ($q) => $q->where('turno', $turno))
            ->with(['escola:id,nome', 'anoLetivo:id,ano'])
            ->orderBy('nome')
            ->get();

        $aulas = HorarioTurma::query()
            ->where('user_id', $professor->id)
            ->whereIn('turma_id', $turmas->pluck('id'))
            ->with(['turma:id,nome,horario_id,escola_id,turno,sala', 'disciplina:id,nome,sigla'])
            ->get();

        // Slots por turma para montar rotulo de horario de cada aula.
        $slotsPorTurma = [];
        $dias = collect();
        $ordens = collect();

        foreach ($turmas as $turma) {
            $slots = $this->slotsDaTurma($turma)->get()
                ->keyBy(fn ($s) => $s->dia_semana.'-'.$s->ordem);
            $slotsPorTurma[$turma->id] = $slots;
            $dias = $dias->merge($slots->pluck('dia_semana'));
            $ordens = $ordens->merge($slots->pluck('ordem'));
        }

        $celulas = [];
        foreach ($aulas as $aula) {
            $slot = $slotsPorTurma[$aula->turma_id][$aula->dia_semana.'-'.$aula->ordem] ?? null;
            $celulas[$aula->dia_semana.'-'.$aula->ordem][] = [
                'turma' => $aula->turma->nome ?? '?',
                'turma_id' => $aula->turma_id,
                'disciplina_id' => $aula->disciplina_id,
                'disciplina' => $aula->disciplina->nome ?? '—',
                'sigla' => $aula->disciplina->sigla ?? null,
                'sala' => $aula->sala ?: $aula->turma->sala,
                'inicio' => $slot ? substr((string) $slot->hora_inicio, 0, 5) : '',
                'fim' => $slot ? substr((string) $slot->hora_fim, 0, 5) : '',
                'chamada_data' => $this->proximaDataDoDia((int) $aula->dia_semana),
            ];
            $dias->push($aula->dia_semana);
            $ordens->push($aula->ordem);
        }

        return [
            'turmas' => $turmas,
            'dias' => $dias->unique()->sort()->values(),
            'ordens' => $ordens->unique()->sort()->values(),
            'celulas' => $celulas,
        ];
    }

    /** Proxima ocorrencia do dia da semana (1=seg..7=dom), hoje inclusa. */
    private function proximaDataDoDia(int $diaSemana): string
    {
        $dow = (int) now()->dayOfWeek; // 0=dom..6=sab
        $hoje = $dow === 0 ? 7 : $dow;

        return now()->addDays(($diaSemana - $hoje + 7) % 7)->toDateString();
    }

    /**
     * Estrutura completa para renderizar a grade da turma.
     */
    public function gradeDaTurma(Turma $turma): array
    {
        $escola = $turma->escola;
        $turno = $turma->turno;

        $slots = $this->temSlots($turma)
            ? $this->slotsDaTurma($turma)
                ->orderBy('dia_semana')->orderBy('ordem')
                ->get()
                ->groupBy('dia_semana')
                ->map(fn (Collection $dia) => $dia->keyBy('ordem'))
            : collect();

        $aulas = HorarioTurma::query()
            ->where('turma_id', $turma->id)
            ->with(['disciplina:id,nome,sigla', 'user:id,nome'])
            ->get()
            ->keyBy(fn ($a) => $a->dia_semana.'-'.$a->ordem);

        // Disciplinas ofertadas com carga e quantidade ja posicionada.
        $alocadas = HorarioTurma::query()
            ->where('turma_id', $turma->id)
            ->selectRaw('disciplina_id, count(*) as total')
            ->groupBy('disciplina_id')
            ->pluck('total', 'disciplina_id');

        $disciplinas = $turma->disciplinas->map(function ($d) use ($alocadas) {
            $carga = $d->pivot->carga_horaria_semanal;
            $usadas = (int) ($alocadas[$d->id] ?? 0);

            return [
                'id' => $d->id,
                'nome' => $d->nome,
                'sigla' => $d->sigla,
                'carga' => $carga,
                'usadas' => $usadas,
                'restante' => $carga ? max(0, (int) $carga - $usadas) : null,
            ];
        })->values();

        // Professores elegiveis por disciplina (atribuicao ativa na turma).
        $professores = $turma->atribuicoes
            ->filter->estaAtiva()
            ->map(fn ($a) => [
                'id' => $a->user_id,
                'nome' => $a->user->nome ?? '—',
                'disciplina_id' => $a->disciplina_id,
            ])->values();

        $ordens = $slots->flatten(1)->pluck('ordem')->unique()->sort()->values();
        $dias = $slots->keys()->sort()->values();

        return [
            'turma' => $turma,
            'escola' => $escola,
            'turno' => $turno,
            'slots' => $slots,
            'aulas' => $aulas,
            'ordens' => $ordens,
            'dias' => $dias,
            'disciplinas' => $disciplinas,
            'professores' => $professores,
            'intervalos' => $this->intervalosDosSlots($slots),
            'temMatriz' => $slots->isNotEmpty(),
        ];
    }

    /**
     * Intervalos (recreios) da grade: espacos de tempo entre slots
     * consecutivos de um mesmo dia. Retorna [dia => [ordem_anterior
     * => ['inicio','fim']]] — a chave e a ordem da aula anterior ao
     * intervalo, usada para renderizar a linha logo abaixo dela.
     */
    private function intervalosDosSlots(Collection $slots): array
    {
        $intervalos = [];

        foreach ($slots as $dia => $porOrdem) {
            $anterior = null;
            foreach ($porOrdem->sortKeys() as $slot) {
                if ($anterior) {
                    $fim = substr((string) $anterior->hora_fim, 0, 5);
                    $ini = substr((string) $slot->hora_inicio, 0, 5);
                    if ($fim !== '' && $ini !== '' && $fim < $ini) {
                        $intervalos[$dia][$anterior->ordem] = ['inicio' => $fim, 'fim' => $ini];
                    }
                }
                $anterior = $slot;
            }
        }

        return $intervalos;
    }

    /**
     * Cria/atualiza a aula de uma celula (dia+ordem) da grade.
     *
     * @throws ValidationException
     */
    public function salvarCelula(Turma $turma, int $dia, int $ordem, array $dados): HorarioTurma
    {
        $slot = $this->slotDaTurma($turma, $dia, $ordem);
        abort_unless($slot, 422, 'Não existe horário neste dia/período para o turno da turma.');

        $disciplina = $turma->disciplinas()->where('disciplinas.id', $dados['disciplina_id'])->first();
        abort_unless($disciplina, 422, 'Disciplina não está ofertada nesta turma.');

        $this->validarCargaHoraria($turma, $disciplina->id, $disciplina->pivot->carga_horaria_semanal, $dia, $ordem);

        $userId = $dados['user_id'] ?? null;
        if ($userId) {
            $this->validarProfessor($turma, $slot, (int) $userId, $dia, $ordem);
        }

        $sala = trim((string) ($dados['sala'] ?? ''));
        if ($sala !== '') {
            $this->validarSala($turma, $slot, $sala, $dia, $ordem);
        }

        // withTrashed: se a celula foi limpa antes (soft delete),
        // reutiliza o registro para nao violar a unique da celula.
        $aula = HorarioTurma::withTrashed()->firstOrNew([
            'turma_id' => $turma->id, 'dia_semana' => $dia, 'ordem' => $ordem,
        ]);
        if ($aula->trashed()) {
            $aula->restore();
        }
        $aula->fill([
            'empresa_id' => $turma->empresa_id,
            'escola_id' => $turma->escola_id,
            'disciplina_id' => $disciplina->id,
            'user_id' => $userId ?: null,
            'sala' => $sala !== '' ? $sala : null,
        ])->save();

        return $aula;
    }

    /** Remove a aula de uma celula, se existir. */
    public function limparCelula(Turma $turma, int $dia, int $ordem): void
    {
        // forceDelete: a unique (turma,dia,ordem) bloquearia recriar
        // a celula se o registro ficasse so soft-deleted.
        HorarioTurma::query()
            ->where('turma_id', $turma->id)
            ->where('dia_semana', $dia)
            ->where('ordem', $ordem)
            ->forceDelete();
    }

    /** Slot da matriz que corresponde à célula (dia, ordem) desta turma. */
    public function slotDaTurma(Turma $turma, int $dia, int $ordem): ?HorarioSlot
    {
        if (! $this->temSlots($turma)) {
            return null;
        }

        return $this->slotsDaTurma($turma)
            ->where('dia_semana', $dia)
            ->where('ordem', $ordem)
            ->first();
    }

    /**
     * Grade pronta para exportacao (PDF/XLSX): celulas por dia e
     * ordem com rotulo, cor da disciplina, professor e sala, mais
     * os intervalos da matriz. $comProfessor inclui o nome do
     * professor na celula.
     */
    public function gradeParaExportacao(Turma $turma, bool $comProfessor = false): array
    {
        $dados = $this->gradeDaTurma($turma);

        $grade = [];
        foreach ($dados['dias'] as $dia) {
            foreach ($dados['ordens'] as $ordem) {
                $slot = $dados['slots']->get($dia)?->get($ordem);
                if (! $slot) {
                    continue;
                }
                $aula = $dados['aulas']->get($dia.'-'.$ordem);
                $grade[$dia][$ordem] = [
                    'inicio' => substr((string) $slot->hora_inicio, 0, 5),
                    'fim' => substr((string) $slot->hora_fim, 0, 5),
                    'rotulo' => $this->rotuloCelula($aula, $comProfessor),
                    'disciplina' => $aula?->disciplina?->sigla ?: $aula?->disciplina?->nome,
                    'professor' => $comProfessor ? $aula?->user?->nome : null,
                    'sala' => $aula?->sala,
                    'cor' => $aula ? self::corDaDisciplina($aula->disciplina_id) : null,
                ];
            }
        }

        return [
            'grade' => $grade,
            'intervalos' => $dados['intervalos'],
            'dias' => $dados['dias'],
            'ordens' => $dados['ordens'],
        ];
    }

    /** Rotulo da celula conforme o modo de exibicao. */
    public function rotuloCelula(?HorarioTurma $aula, bool $comProfessor = false): string
    {
        if (! $aula) {
            return '';
        }

        $disciplina = $aula->disciplina?->sigla ?: $aula->disciplina?->nome ?: '—';
        if (! $comProfessor) {
            return $disciplina;
        }

        $professor = $aula->user?->nome;

        return $professor ? "{$disciplina} — {$professor}" : $disciplina;
    }

    /** Turmas ativas da escola atual com contagem de aulas posicionadas. */
    public function turmasComGrade(): Collection
    {
        return Turma::query()
            ->where('ativo', 'S')
            ->with(['escola', 'anoLetivo:id,ano'])
            ->withCount(['matriculas'])
            ->orderBy('nome')
            ->get()
            ->map(function (Turma $t) {
                $t->aulas_count = HorarioTurma::query()->where('turma_id', $t->id)->count();
                $t->tem_matriz = $this->temSlots($t)
                    && $this->slotsDaTurma($t)->exists();

                return $t;
            });
    }

    /** Turma tem referencia de grade (horario vinculado ou escola+turno legado). */
    private function temSlots(Turma $turma): bool
    {
        return (bool) ($turma->horario_id || ($turma->escola_id && $turma->turno));
    }

    /**
     * Query dos slots da turma: horario vinculado quando definido,
     * senao a matriz legada por escola+turno.
     */
    private function slotsDaTurma(Turma $turma): \Illuminate\Database\Eloquent\Builder
    {
        $q = HorarioSlot::query();
        if ($turma->horario_id) {
            return $q->where('horario_id', $turma->horario_id);
        }

        return $q->where('escola_id', $turma->escola_id)->where('turno', $turma->turno);
    }

    /**
     * Nao deixa passar mais aulas da disciplina do que a carga
     * horaria semanal ofertada (carga nula = sem limite).
     */
    private function validarCargaHoraria(Turma $turma, int $disciplinaId, ?int $carga, int $dia, int $ordem): void
    {
        if (! $carga || $carga <= 0) {
            return;
        }

        $usadas = HorarioTurma::query()
            ->where('turma_id', $turma->id)
            ->where('disciplina_id', $disciplinaId)
            ->where(function ($q) use ($dia, $ordem) {
                $q->where('dia_semana', '!=', $dia)->orWhere('ordem', '!=', $ordem);
            })
            ->count();

        // Se a celula atual ja tem esta disciplina, ela ja esta contada
        // na consulta acima apenas quando dia/ordem diferem — ajustar:
        $atual = HorarioTurma::query()
            ->where('turma_id', $turma->id)
            ->where('dia_semana', $dia)
            ->where('ordem', $ordem)
            ->where('disciplina_id', $disciplinaId)
            ->exists();

        $totalApos = $usadas + ($atual ? 0 : 1);
        if ($totalApos > $carga) {
            throw ValidationException::withMessages([
                'disciplina_id' => "Carga horária excedida: esta disciplina tem {$carga}h/semana e já há {$usadas} aula(s) na grade.",
            ]);
        }
    }

    /** Professor nao pode estar em duas aulas ao mesmo tempo na escola. */
    private function validarProfessor(Turma $turma, HorarioSlot $slot, int $userId, int $dia, int $ordem): void
    {
        $conflitos = HorarioTurma::query()
            ->where('user_id', $userId)
            ->where('dia_semana', $dia)
            ->where('turma_id', '!=', $turma->id)
            ->with('turma:id,turno,horario_id,escola_id,nome')
            ->get()
            ->filter(function (HorarioTurma $a) use ($turma, $slot, $ordem) {
                if ($a->ordem === $ordem && $this->mesmaGrade($turma, $a->turma)) {
                    return true; // mesmo slot => mesmo horario
                }

                $slotOutro = $a->turma ? $this->slotDaTurma($a->turma, $a->dia_semana, $a->ordem) : null;
                if (! $slotOutro) {
                    return false;
                }

                return $this->intervalosSeSobrepoem($slot, $slotOutro);
            });

        // Mesma celula da propria turma nao e conflito (updateOrCreate).
        $conflitos = $conflitos->reject(fn ($a) => $a->turma_id === $turma->id);

        if ($conflitos->isNotEmpty()) {
            $onde = $conflitos->map(fn ($a) => $a->turma->nome ?? '?')->unique()->implode(', ');
            throw ValidationException::withMessages([
                'user_id' => "Professor já tem aula neste horário em: {$onde}.",
            ]);
        }
    }

    /** Sala nao pode receber duas turmas no mesmo horario. */
    private function validarSala(Turma $turma, HorarioSlot $slot, string $sala, int $dia, int $ordem): void
    {
        $normalizada = mb_strtolower(trim($sala));

        $conflitos = HorarioTurma::query()
            ->whereRaw('LOWER(TRIM(sala)) = ?', [$normalizada])
            ->where('dia_semana', $dia)
            ->where('turma_id', '!=', $turma->id)
            ->with('turma:id,turno,horario_id,escola_id,nome')
            ->get()
            ->filter(function (HorarioTurma $a) use ($turma, $slot, $ordem) {
                if ($a->ordem === $ordem && $this->mesmaGrade($turma, $a->turma)) {
                    return true;
                }

                $slotOutro = $a->turma ? $this->slotDaTurma($a->turma, $a->dia_semana, $a->ordem) : null;

                return $slotOutro && $this->intervalosSeSobrepoem($slot, $slotOutro);
            });

        if ($conflitos->isNotEmpty()) {
            $onde = $conflitos->map(fn ($a) => $a->turma->nome ?? '?')->unique()->implode(', ');
            throw ValidationException::withMessages([
                'sala' => "Sala já ocupada neste horário por: {$onde}.",
            ]);
        }
    }

    /** Duas turmas dividem a mesma matriz de slots (mesmo horario ou mesmo turno legado). */
    private function mesmaGrade(Turma $a, ?Turma $b): bool
    {
        if (! $b) {
            return false;
        }

        if ($a->horario_id && $b->horario_id) {
            return $a->horario_id === $b->horario_id;
        }

        return $a->escola_id === $b->escola_id && $a->turno === $b->turno;
    }

    private function intervalosSeSobrepoem(HorarioSlot $a, HorarioSlot $b): bool
    {
        return substr((string) $a->hora_inicio, 0, 5) < substr((string) $b->hora_fim, 0, 5)
            && substr((string) $b->hora_inicio, 0, 5) < substr((string) $a->hora_fim, 0, 5);
    }
}
