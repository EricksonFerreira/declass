<?php

namespace App\Actions\Reserva\Checkin\Contrato\Excluir;

use App\Enums\StatusReservaEnum;
use App\Models\ContratoCheckin;
use App\Services\MovimentacaoService;
use App\Services\ReservaService;
use App\Support\SchemaCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExcluirContratoCheckinAction
{
    public function __construct(
        private MovimentacaoService $movimentacaoService,
        private ReservaService $reservaService,
    ) {}

    public function handle(Request $request, $id)
    {
        $contrato = ContratoCheckin::with(['reserva.codigoReserva', 'reserva.veiculo', 'reserva.contratoCheckout', 'veiculo'])
            ->where('id', $id)
            ->orWhere('reserva_id', $id)
            ->orWhereHas('reserva.codigoReserva', function ($q) use ($id) {
                $q->where('codigo', $id);
            })
            ->first();

        if (! $contrato) {
            return response()->json(['error' => 'Contrato nao encontrado.'], 404);
        }

        $reserva = $contrato->reserva;
        $foiAssinado = $contrato->assinado === 'S';
        $veiculoNome = $contrato->veiculo?->nome ?? ($contrato->veiculo_id ? "ID {$contrato->veiculo_id}" : 'N/D');

        if ($contrato->pdf_path && Storage::disk('public')->exists($contrato->pdf_path)) {
            Storage::disk('public')->delete($contrato->pdf_path);
        }

        $contrato->delete();

        $movimentacoesCanceladas = 0;
        if ($reserva) {
            $movimentacoesCanceladas = $this->movimentacaoService->liberarBloqueioDeDisponibilidadePorReserva(
                (int) $reserva->id,
                [2, 3],
                'Cancelada por exclusão do contrato de check-in'
            );

            $updates = [
                'status_reserva_id' => StatusReservaEnum::Confirmada->value,
            ];

            if (SchemaCache::hasColumn('reservas', 'veiculo_id')) {
                $updates['veiculo_id'] = null;
            }

            $reserva->update($updates);

            $this->reservaService->adicionaLog(
                (int) $reserva->id,
                Auth::id(),
                json_encode([
                    'acao' => 'Contrato de check-in excluído',
                    'foi_assinado' => $foiAssinado,
                    'veiculo' => $veiculoNome,
                    'movimentacoes_canceladas' => $movimentacoesCanceladas,
                    'excluido_por' => Auth::user()?->name ?? Auth::user()?->nome ?? 'Usuário',
                    'status_alterado_para' => 'Confirmada',
                ]),
                'Contrato de check-in excluído'.($foiAssinado ? ' (estava assinado)' : '').' — status voltou para Confirmada',
                $foiAssinado ? 'danger' : 'warning'
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Contrato excluido com sucesso.',
                'movimentacoes_canceladas' => $movimentacoesCanceladas,
            ]);
        }

        return redirect()->route('reserva.checkin', $reserva?->id ?? $id)
            ->with('success', 'Contrato de check-in excluído com sucesso.');
    }
}
