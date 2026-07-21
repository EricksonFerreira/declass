<?php

namespace App\Actions\ReservaMotoristaApp;

use App\Models\ReservaMotoristaApp;
use App\Models\ReservaMotoristaAppPagamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RenovarMotoristaAppAction
{
    public function handle(
        ReservaMotoristaApp $reserva,
        callable $valorUnitarioCalc,
        callable $aplicarPeriodo,
        callable $rotuloPeriodo,
    ): ReservaMotoristaAppPagamento {
        $plano = $reserva->planoMotoristaApp;
        if (!$plano) {
            throw new \RuntimeException('Plano nao encontrado para esta reserva.');
        }

        $periodos = max(1, (int) request('periodos', 1));
        $observacoes = (string) request('observacoes', '');

        $valorUnitario = $valorUnitarioCalc($reserva);
        if ($valorUnitario <= 0) {
            throw new \RuntimeException('Nao foi possivel identificar o valor do plano para renovacao.');
        }

        $periodoContrato = (string) ($reserva->periodo_contrato ?: $plano->periodo ?: 'semana');
        $inicioBase = $reserva->data_fim ? Carbon::parse($reserva->data_fim) : Carbon::now();

        if (empty($reserva->data_fim)) {
            throw new \RuntimeException('Nao foi possivel identificar o fim do ciclo atual.');
        }

        $periodoUnidade = $rotuloPeriodo($periodoContrato);
        $novaVigenciaFim = $aplicarPeriodo($inicioBase->copy(), $periodoContrato, $periodos);
        $quantidadeDias = max(1, $inicioBase->diffInDays($novaVigenciaFim));
        $valorTotal = round($valorUnitario * $periodos, 2);

        $pagamento = ReservaMotoristaAppPagamento::create([
            'reserva_motorista_app_id' => (int) $reserva->id,
            'empresa_id' => $reserva->empresa_id ?? Auth::user()?->empresa_id ?? null,
            'user_id' => (int) (Auth::id() ?? $reserva->user_id),
            'periodos' => $periodos,
            'periodo_unidade' => $periodoUnidade,
            'valor_unitario' => $valorUnitario,
            'valor_total' => $valorTotal,
            'data_inicio_prevista' => $inicioBase->copy(),
            'data_fim_prevista' => $novaVigenciaFim,
            'status' => 'pendente',
            'observacoes' => $observacoes ?: null,
            'metadados' => [
                'plano_id' => (int) $plano->id,
                'valor_plano_atual' => $valorUnitario,
                'quantidade_dias_prevista' => $quantidadeDias,
            ],
        ]);

        return $pagamento;
    }
}
