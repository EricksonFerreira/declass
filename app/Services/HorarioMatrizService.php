<?php

namespace App\Services;

use App\Models\Escola;
use App\Models\Horario;
use App\Models\HorarioSlot;
use App\Models\Turma;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Matriz de horarios: faixas de horario (slots) agrupadas em um
 * Horario nomeado de uma escola/turno. Um turno pode ter varias
 * matrizes; a turma se vincula a uma especifica.
 */
class HorarioMatrizService
{
    /** Horarios da escola com contagens de slots e turmas vinculadas. */
    public function horariosDaEscola(Escola $escola): Collection
    {
        return Horario::query()
            ->where('escola_id', $escola->id)
            ->with('slots:id,horario_id,dia_semana,ordem,hora_inicio,hora_fim')
            ->withCount('turmas')
            ->orderBy('turno')->orderBy('nome')
            ->get();
    }

    /**
     * Grade de slots do horario, organizada como
     * [dia_semana => [ordem => HorarioSlot]].
     */
    public function gradeDoHorario(Horario $horario): array
    {
        return $horario->slots()
            ->get()
            ->groupBy('dia_semana')
            ->map(fn (Collection $dia) => $dia->keyBy('ordem'))
            ->all();
    }

    /**
     * Cria o horario nomeado e grava sua matriz de slots
     * ([dia_semana => [[inicio,fim],...]]).
     */
    public function criarHorario(Escola $escola, array $dados, array $grade): Horario
    {
        return DB::transaction(function () use ($escola, $dados, $grade) {
            $horario = Horario::create([
                'empresa_id' => $escola->empresa_id,
                'escola_id' => $escola->id,
                'turno' => $dados['turno'],
                'nome' => $dados['nome'],
                'ativo' => 'S',
            ]);

            $this->gravarSlots($horario, $grade);

            return $horario;
        });
    }

    /**
     * Atualiza nome/turno do horario e substitui integralmente
     * sua matriz de slots.
     */
    public function salvarHorario(Horario $horario, array $dados, array $grade): void
    {
        DB::transaction(function () use ($horario, $dados, $grade) {
            $horario->update([
                'nome' => $dados['nome'],
                'turno' => $dados['turno'],
            ]);

            $horario->slots()->delete();
            $this->gravarSlots($horario, $grade);
        });
    }

    /** Remove o horario se nenhuma turma estiver vinculada. */
    public function excluirHorario(Horario $horario): void
    {
        abort_if($horario->turmas()->exists(), 422,
            'Este horário tem turmas vinculadas. Desvincule-as antes de excluir.');

        DB::transaction(fn () => $horario->delete());
    }

    /** Grava slots do horario mantendo escola_id/turno denormalizados. */
    private function gravarSlots(Horario $horario, array $grade): void
    {
        foreach ($grade as $dia => $slots) {
            foreach (array_values($slots) as $i => $slot) {
                HorarioSlot::create([
                    'empresa_id' => $horario->empresa_id,
                    'escola_id' => $horario->escola_id,
                    'horario_id' => $horario->id,
                    'turno' => $horario->turno,
                    'dia_semana' => (int) $dia,
                    'ordem' => $i + 1,
                    'hora_inicio' => $slot['inicio'],
                    'hora_fim' => $slot['fim'],
                ]);
            }
        }
    }
}
