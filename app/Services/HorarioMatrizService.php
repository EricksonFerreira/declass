<?php

namespace App\Services;

use App\Models\Escola;
use App\Models\HorarioSlot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Matriz de horarios: faixas de horario (slots) por escola e turno.
 * Os slots sao a "grade vazia" que as turmas preenchem com aulas.
 */
class HorarioMatrizService
{
    /**
     * Slots configurados de uma escola, agrupados por turno.
     * Retorna [turno => ['dias' => int[], 'slots' => HorarioSlot[]]].
     */
    public function matrizDaEscola(Escola $escola): array
    {
        $slots = HorarioSlot::query()
            ->where('escola_id', $escola->id)
            ->orderBy('dia_semana')->orderBy('ordem')
            ->get();

        $matriz = [];
        foreach ($slots as $slot) {
            $matriz[$slot->turno] ??= ['dias' => [], 'slots' => collect()];
            $matriz[$slot->turno]['dias'][$slot->dia_semana] = true;
            $matriz[$slot->turno]['slots']->push($slot);
        }

        foreach ($matriz as &$item) {
            $item['dias'] = array_keys($item['dias']);
            sort($item['dias']);
        }

        return $matriz;
    }

    /**
     * Grade de slots de um turno, organizada como
     * [dia_semana => [ordem => HorarioSlot]].
     */
    public function gradeDoTurno(Escola $escola, string $turno): array
    {
        return HorarioSlot::query()
            ->where('escola_id', $escola->id)
            ->where('turno', $turno)
            ->orderBy('dia_semana')->orderBy('ordem')
            ->get()
            ->groupBy('dia_semana')
            ->map(fn (Collection $dia) => $dia->keyBy('ordem'))
            ->all();
    }

    /**
     * Substitui integralmente a matriz de um turno: remove os slots
     * atuais e recria a partir de [dia_semana => [[inicio,fim],...]].
     * Validacao de formato/consistencia fica no Form Request.
     */
    public function salvarMatriz(Escola $escola, string $turno, array $grade): void
    {
        DB::transaction(function () use ($escola, $turno, $grade) {
            HorarioSlot::query()
                ->where('escola_id', $escola->id)
                ->where('turno', $turno)
                ->delete();

            foreach ($grade as $dia => $slots) {
                foreach (array_values($slots) as $i => $slot) {
                    HorarioSlot::create([
                        'empresa_id' => $escola->empresa_id,
                        'escola_id' => $escola->id,
                        'turno' => $turno,
                        'dia_semana' => (int) $dia,
                        'ordem' => $i + 1,
                        'hora_inicio' => $slot['inicio'],
                        'hora_fim' => $slot['fim'],
                    ]);
                }
            }
        });
    }

    /** Turnos que ja tem ao menos um slot nesta escola. */
    public function turnosConfigurados(Escola $escola): array
    {
        return HorarioSlot::query()
            ->where('escola_id', $escola->id)
            ->distinct()
            ->pluck('turno')
            ->all();
    }
}
