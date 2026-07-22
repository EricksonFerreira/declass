<?php

namespace App\Actions\Reserva\Checkout\Realizar;

use App\Models\ReservaMotoristaApp;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RealizarCheckoutMotoristaAppAction
{
    public function __construct(
        private ReservaService $reservaService,
    ) {}

    public function handle(ReservaMotoristaApp $reserva, array $dados, Request $request): ReservaMotoristaApp
    {
        if (empty($reserva->checkin_realizado_em)) {
            throw new \RuntimeException('O check-in precisa ser registrado antes do checkout.');
        }

        $documentos = (array) ($reserva->documentos_json ?? []);
        $fotos = [];
        $dir = 'motorista-app/checkout/reserva_' . (int) $reserva->id;

        foreach (['foto_direita_co', 'foto_esquerda_co', 'foto_frente_co', 'foto_tras_co', 'foto_velocimetro_co', 'foto_combustivel_co'] as $input) {
            if ($request->hasFile($input)) {
                $fotos[$input] = \App\Support\ArquivoAnexoHelper::armazenar($request->file($input), $dir);
            }
        }

        data_set($documentos, 'checkout.fotos', $fotos);
        data_set($documentos, 'checkout.notas_devolucao', $dados['notas_devolucao'] ?? null);
        data_set($documentos, 'checkout.observacoes_devolucao', $dados['observacoes_devolucao'] ?? null);
        data_set($documentos, 'checkout.registrado_em', now()->format('Y-m-d H:i:s'));
        $kmFinal = $dados['quilometragem_final'] ?? $dados['quilometragem_devolucao'] ?? null;
        $combustivelFinal = $dados['nivel_combustivel_final'] ?? $dados['nivel_combustivel_devolucao'] ?? null;

        $reserva->update([
            'quilometragem_final' => $kmFinal !== null ? (int) $kmFinal : null,
            'nivel_combustivel_final' => $combustivelFinal !== null ? (string) $combustivelFinal : null,
            'checkout_realizado_em' => now(),
            'status' => 'encerrada',
            'documentos_json' => $documentos,
        ]);

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao' => 'Checkout registrado',
            'quilometragem_final' => $kmFinal,
            'nivel_combustivel_final' => $combustivelFinal,
            'data' => now()->format('d/m/Y H:i'),
        ]));

        return $reserva;
    }
}
