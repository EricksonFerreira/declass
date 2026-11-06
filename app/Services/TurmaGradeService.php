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
     * Estrutura completa para renderizar a grade da turma.
     */
    public function gradeDaTurma(Turma $turma): array
    {
        $escola = $turma->escola;
        $turno = $turma->turno;

        $slots = $escola && $turno
            ? HorarioSlot::query()
                ->where('escola_id', $escola->id)
                ->where('turno', $turno)
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
            'temMatriz' => $slots->isNotEmpty(),
        ];
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
        if (! $turma->escola_id || ! $turma->turno) {
            return null;
        }

        return HorarioSlot::query()
            ->where('escola_id', $turma->escola_id)
            ->where('turno', $turma->turno)
            ->where('dia_semana', $dia)
            ->where('ordem', $ordem)
            ->first();
    }

    /**
     * Grade pronta para exportacao: [dia => [ordem => rotulo]].
     * $comProfessor=true inclui o nome do professor na celula.
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
                ];
            }
        }

        return $grade;
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
                $t->tem_matriz = $t->escola_id && $t->turno
                    && HorarioSlot::query()->where('escola_id', $t->escola_id)->where('turno', $t->turno)->exists();

                return $t;
            });
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
            ->with('turma:id,turno,escola_id,nome')
            ->get()
            ->filter(function (HorarioTurma $a) use ($turma, $slot, $ordem) {
                if ($a->ordem === $ordem && $a->turma?->escola_id === $turma->escola_id && $a->turma?->turno === $turma->turno) {
                    return true; // mesmo slot => mesmo horario
                }

                $slotOutro = $this->slotDe($a->turma?->escola_id, $a->turma?->turno, $a->dia_semana, $a->ordem);
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
            ->with('turma:id,turno,escola_id,nome')
            ->get()
            ->filter(function (HorarioTurma $a) use ($turma, $slot, $ordem) {
                if ($a->ordem === $ordem && $a->turma?->escola_id === $turma->escola_id && $a->turma?->turno === $turma->turno) {
                    return true;
                }

                $slotOutro = $this->slotDe($a->turma?->escola_id, $a->turma?->turno, $a->dia_semana, $a->ordem);

                return $slotOutro && $this->intervalosSeSobrepoem($slot, $slotOutro);
            });

        if ($conflitos->isNotEmpty()) {
            $onde = $conflitos->map(fn ($a) => $a->turma->nome ?? '?')->unique()->implode(', ');
            throw ValidationException::withMessages([
                'sala' => "Sala já ocupada neste horário por: {$onde}.",
            ]);
        }
    }

    private function slotDe(?int $escolaId, ?string $turno, int $dia, int $ordem): ?HorarioSlot
    {
        if (! $escolaId || ! $turno) {
            return null;
        }

        return HorarioSlot::query()
            ->where('escola_id', $escolaId)
            ->where('turno', $turno)
            ->where('dia_semana', $dia)
            ->where('ordem', $ordem)
            ->first();
    }

    private function intervalosSeSobrepoem(HorarioSlot $a, HorarioSlot $b): bool
    {
        return substr((string) $a->hora_inicio, 0, 5) < substr((string) $b->hora_fim, 0, 5)
            && substr((string) $b->hora_inicio, 0, 5) < substr((string) $a->hora_fim, 0, 5);
    }
}
