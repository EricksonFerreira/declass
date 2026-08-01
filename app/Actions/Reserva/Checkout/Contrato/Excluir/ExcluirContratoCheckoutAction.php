<?php

namespace App\Actions\Reserva\Checkout\Contrato\Excluir;

use App\Enums\StatusReservaEnum;
use App\Models\ContratoCheckout;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExcluirContratoCheckoutAction
{
    public function __construct(
        private ReservaService $reservaService,
    ) {}

    public function handle(Request $request, $codigo)
    {
        $request->validate([
            'justificativa' => ['nullable', 'string', 'min:5', 'max:1000'],
        ]);

        $contrato = ContratoCheckout::query()
            ->with(['reserva.codigoReserva'])
            ->where('id', $codigo)
            ->orWhere('reserva_id', $codigo)
            ->first();

        if (! $contrato && is_string($codigo) && $codigo !== '') {
            $contrato = ContratoCheckout::query()
                ->with(['reserva.codigoReserva'])
                ->whereHas('reserva.codigoReserva', function ($q) use ($codigo) {
                    $q->where('codigo', $codigo);
                })
                ->first();
        }

        if (! $contrato) {
            return response()->json(['error' => 'Contrato nao encontrado.'], 404);
        }

        $reserva = $contrato->reserva()->with(['codigoReserva'])->first();
        if (! $reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        $foiAssinado = $contrato->assinado === 'S';
        $justificativa = trim((string) $request->input('justificativa', ''));

        if ($foiAssinado && $justificativa === '') {
            return response()->json([
                'error' => 'Informe uma justificativa para invalidar um checkout assinado.',
            ], 422);
        }

        DB::transaction(function () use ($contrato, $reserva, $foiAssinado) {
            $fotoCols = [
                'foto_direita_path',
                'foto_esquerda_path',
                'foto_frente_path',
                'foto_tras_path',
                'foto_velocimetro_path',
                'foto_combustivel_path',
            ];

            foreach ($fotoCols as $col) {
                if (! empty($contrato->$col)) {
                    Storage::disk('public')->delete($contrato->$col);
                }
            }

            if (! empty($contrato->assinatura)) {
                Storage::disk('public')->delete($contrato->assinatura);
            }

            if (! empty($contrato->pdf_path)) {
                Storage::disk('public')->delete($contrato->pdf_path);
            }

            if ($foiAssinado) {
                $contrato->forceFill([
                    'assinado' => 'N',
                    'data_assinatura' => null,
                    'assinatura' => null,
                    'pdf_path' => null,
                    // 'pdf_hash' => null,
                ])->save();
            } else {
                $contrato->delete();
            }

            $reserva->forceFill([
                'status_reserva_id' => StatusReservaEnum::Checkin->value,
            ])->save();
        });

        $reserva->refresh();

        $this->reservaService->adicionaLog(
            (int) $reserva->id,
            Auth::id(),
            json_encode([
                'acao' => $foiAssinado ? 'Contrato de check-out invalidado' : 'Contrato de check-out excluido',
                'excluido_por' => Auth::user()?->name ?? Auth::user()?->nome ?? 'Usuario',
                'foi_assinado' => $foiAssinado,
                'justificativa' => $justificativa ?: null,
                'status_operacional' => $foiAssinado ? 'Em conferencia' : 'Reaberto',
            ]),
            $foiAssinado
                ? 'Contrato de check-out invalidado e reserva colocada em conferência'
                : 'Contrato de check-out excluído e reserva reaberta',
            $foiAssinado ? 'danger' : 'warning'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $foiAssinado
                    ? 'Contrato invalidado com sucesso.'
                    : 'Contrato excluido com sucesso.',
            ]);
        }

        return redirect()
            ->route('reserva.show', $reserva->codigoReserva?->codigo ?? $reserva->id)
            ->with(
                'warning',
                $foiAssinado
                    ? 'Contrato de check-out invalidado. A reserva segue em conferência.'
                    : 'Contrato de check-out excluído. A reserva foi reaberta para edição.'
            );
    }
}
