<?php

namespace App\Actions\Reserva\Pagamento\Confirmar\Admin;

use App\Models\ReservaMotoristaAppPagamento;
use App\Services\Reserva\ReservaResolver;
use Illuminate\Http\Request;

class ConfirmarPagamentoAdminAction
{
    public function __construct(
        private ReservaResolver $reservaResolver,
        private ConfirmarPagamentoAdminReservaComumAction $comumAdminAction,
        private ConfirmarPagamentoAdminMotoristaAppAction $motoristaAppAdminAction,
    ) {}

    public function handle(string|int $pagamentoId, Request $request): mixed
    {
        $pagamento = ReservaMotoristaAppPagamento::find((int) $pagamentoId);
        if ($pagamento) {
            return $this->motoristaAppAdminAction->handle($pagamento, $request);
        }

        $reservaResolvida = $this->reservaResolver->resolver((string) $pagamentoId);

        if ($reservaResolvida->ehComum()) {
            return $this->comumAdminAction->handle($reservaResolvida->registro, $request);
        }

        if ($reservaResolvida->ehMotoristaApp()) {
            $pagamento = $reservaResolvida->registro->pagamentos()
                ->filter(fn ($item) => strtolower((string) ($item->status ?? '')) === 'pendente')
                ->sortByDesc('id')
                ->first();

            if (! $pagamento) {
                $codigoReserva = $reservaResolvida->registro->codigoReserva?->codigo ?? ('MAP-'.str_pad((string) $reservaResolvida->registro->id, 6, '0', STR_PAD_LEFT));

                return redirect()
                    ->route('reserva.show', $codigoReserva)
                    ->with('success', 'Não há pagamento pendente para esta reserva.');
            }

            return $this->motoristaAppAdminAction->handle($pagamento, $request);
        }

        throw new \LogicException('Tipo de reserva não suportado para confirmação de pagamento admin.');
    }
}



