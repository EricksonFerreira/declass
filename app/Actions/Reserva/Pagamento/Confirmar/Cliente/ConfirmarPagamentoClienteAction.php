<?php

namespace App\Actions\Reserva\Pagamento\Confirmar\Cliente;

use App\Models\ReservaMotoristaAppPagamento;
use App\Services\Reserva\ReservaResolver;
use Illuminate\Http\Request;

class ConfirmarPagamentoClienteAction
{
    public function __construct(
        private ReservaResolver $reservaResolver,
        private ConfirmarPagamentoClienteReservaComumAction $comumClienteAction,
        private ConfirmarPagamentoClienteMotoristaAppAction $motoristaAppClienteAction,
    ) {}

    public function handle(string|int $identificador, Request $request): mixed
    {
        if (is_numeric($identificador)) {
            $pagamento = ReservaMotoristaAppPagamento::find((int) $identificador);
            if ($pagamento) {
                return $this->motoristaAppClienteAction->handle($pagamento, $request);
            }
        }

        $reservaResolvida = $this->reservaResolver->resolver((string) $identificador);

        if ($reservaResolvida->ehComum()) {
            return $this->comumClienteAction->handle($reservaResolvida->registro, $request);
        }

        if ($reservaResolvida->ehMotoristaApp()) {
            $pagamento = $reservaResolvida->registro->pagamentos()
                ->filter(fn ($item) => strtolower((string) ($item->status ?? '')) === 'pendente')
                ->sortByDesc('id')
                ->first();

            if (! $pagamento) {
                $codigoReserva = $reservaResolvida->registro->codigoReserva?->codigo ?? ('MAP-'.str_pad((string) $reservaResolvida->registro->id, 6, '0', STR_PAD_LEFT));

                return redirect()
                    ->route('reserva.minhas.show', $codigoReserva)
                    ->with('success', 'Não há pagamento pendente para esta reserva.');
            }

            return $this->motoristaAppClienteAction->handle($pagamento, $request);
        }

        throw new \LogicException('Tipo de reserva não suportado para confirmação de pagamento do cliente.');
    }
}


