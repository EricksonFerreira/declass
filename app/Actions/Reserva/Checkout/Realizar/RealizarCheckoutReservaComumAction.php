<?php

namespace App\Actions\Reserva\Checkout\Realizar;

use App\Models\ContratoCheckout;
use App\Models\Reserva;
use App\Services\CalcaoService;
use App\Services\MovimentacaoService;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RealizarCheckoutReservaComumAction
{
    public function __construct(
        private ReservaService $reservaService,
        private MovimentacaoService $movimentacaoService,
        private CalcaoService $calcaoService,
    ) {}

    public function handle(Reserva $reserva, array $dados, Request $request): ContratoCheckout
    {
        if (!$reserva->podeFazerCheckout()) {
            throw new \RuntimeException('Reserva não está em status válido para check-out.');
        }

        if (ContratoCheckout::where('reserva_id', $reserva->id)->exists()) {
            throw new \RuntimeException('Check-out desta reserva já foi registrado.');
        }

        if (!$reserva->veiculo_id) {
            throw new \RuntimeException('Veículo não vinculado à reserva.');
        }

        $kmInicial = $reserva->contratoCheckin?->quilometragem_atual;
        if ($kmInicial !== null) {
            $kmDevolucao = (float) ($dados['quilometragem_devolucao'] ?? 0);
            if ($kmDevolucao < (float) $kmInicial) {
                throw new \RuntimeException('A quilometragem na devolução deve ser maior ou igual à quilometragem inicial do check-in (KM ' . $kmInicial . ').');
            }
        }

        return DB::transaction(function () use ($reserva, $dados, $request) {
            $dir = 'checkout/reserva_' . (int) $reserva->id;
            $dadosFotosCheckout = $this->salvarFotosCheckout($request, $dir);

            $contratoCheckout = ContratoCheckout::create([
                'reserva_id' => (int) $reserva->id,
                'veiculo_id' => (int) $reserva->veiculo_id,
                'empresa_id' => $reserva->empresa_id ?? null,
                'quilometragem_devolucao' => $dados['quilometragem_devolucao'],
                'nivel_combustivel_devolucao' => $dados['nivel_combustivel_devolucao'],
                'data_devolucao' => $dados['data_devolucao'],
                'hora_devolucao' => $dados['hora_devolucao'],
                'notas_devolucao' => $dados['notas_devolucao'] ?? null,
                'observacoes_devolucao' => $dados['observacoes_devolucao'] ?? null,
                ...$dadosFotosCheckout,
            ]);

            $this->movimentacaoService->realizarCheckOut(
                (int) $reserva->veiculo_id,
                (int) $reserva->estacao_devolucao_id,
                (int) $reserva->id,
                'Check-out com vistoria de devolução'
            );

            $this->reservaService->checkOutRealizado($reserva->id);

            $this->liberarCalcao($reserva);

            $this->reservaService->adicionaLog(
                $reserva->id,
                Auth::id(),
                json_encode(['acao' => 'Check-out com vistoria concluído'])
            );

            return $contratoCheckout;
        });
    }

    private function salvarFotosCheckout(Request $request, string $dir): array
    {
        $mapFotos = [
            'foto_direita_co' => 'foto_direita_path',
            'foto_esquerda_co' => 'foto_esquerda_path',
            'foto_frente_co' => 'foto_frente_path',
            'foto_tras_co' => 'foto_tras_path',
            'foto_velocimetro_co' => 'foto_velocimetro_path',
            'foto_combustivel_co' => 'foto_combustivel_path',
        ];

        $destDir = storage_path('app/public/' . $dir);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0775, true);
        }

        $dadosFotos = [];
        foreach ($mapFotos as $input => $col) {
            if ($request->hasFile($input) && $request->file($input)->isValid()) {
                $file = $request->file($input);
                $extension = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = Str::random(40) . '.' . $extension;
                $file->move($destDir, $filename);
                $dadosFotos[$col] = $dir . '/' . $filename;
            }
        }

        return $dadosFotos;
    }

    private function liberarCalcao(Reserva $reserva): void
    {
        $calcao = $reserva->calcao;
        if (!$calcao) {
            return;
        }

        if (!empty($calcao->payment_intent_id) && $calcao->podeLiberar()) {
            $this->calcaoService->cancelar(
                $calcao,
                'Calção liberado automaticamente após o checkout.'
            );

            $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
                'acao' => 'Calção liberado automaticamente no checkout',
                'calcao_id' => $calcao->id,
            ]));

            return;
        }

        if (!empty($calcao->payment_intent_id) || $calcao->status === \App\Models\Calcao::STATUS_CANCELADO) {
            return;
        }

        $valor = (float) ($calcao->valor_capturado ?: $calcao->valor_autorizado ?: $calcao->valor_calculado ?: 0);
        if ($valor <= 0) {
            return;
        }

        $calcao->update([
            'status' => \App\Models\Calcao::STATUS_CANCELADO,
            'data_hora_cancelamento' => now(),
            'usuario_responsavel_id' => Auth::id(),
            'observacao' => 'Calção presencial devolvido automaticamente após o checkout.',
        ]);

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao' => 'Calção presencial devolvido automaticamente no checkout',
            'calcao_id' => $calcao->id,
            'valor_devolvido' => $valor,
        ]));
    }
}
