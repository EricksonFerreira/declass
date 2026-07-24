<?php
namespace App\Services;

use App\Models\Plano;
use App\Models\Opcional;
use App\Models\Cupom;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RenovacaoService
{
    /**
     * Calculate new vigência dates based on the target plan and number of cycles.
     */
    public function calcularVigencia(Reserva $reserva, Plano $plano, int $ciclos = 1): array
    {
        $oldEnd = Carbon::parse($reserva->data_fim);
        $newStart = $oldEnd->copy()->addDay();
        // Determine periodicity (mensal or anual) based on plan values
        $period = $plano->valor_mensal ? 'mensal' : 'anual';
        $days = ($period === 'mensal') ? 30 * $ciclos : 365 * $ciclos;
        $newEnd = $newStart->copy()->addDays($days - 1);
        return [
            'data_inicio' => $newStart,
            'data_fim' => $newEnd,
            'periodicidade' => $period,
        ];
    }

    /**
     * Calculate total value for the reservation after renewal.
     */
    public function calcularValorTotal(Plano $plano, array $opcionaisIds = [], ?Cupom $cupom = null): float
    {
        $base = $plano->valor_mensal ?? $plano->valor_anual ?? 0;
        $opcionaisTotal = Opcional::whereIn('id', $opcionaisIds)->sum('valor');
        $total = $base + $opcionaisTotal;
        if ($cupom) {
            if ($cupom->tipo === 'percentual') {
                $total = $total * (1 - $cupom->desconto / 100);
            } else {
                $total = max(0, $total - $cupom->desconto);
            }
        }
        return round($total, 2);
    }
}
?>
