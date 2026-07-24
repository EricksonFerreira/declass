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

    public function handle(string|int $identificador, Request $request): mixed
    {
        /*
         * Primeiro tenta identificar uma reserva pelo código recebido.
         *
         * Isso é importante porque o número recebido também pode coincidir
         * com o ID de algum pagamento antigo.
         */
        $reservaResolvida = $this->reservaResolver->resolver(
            (string) $identificador
        );

        if ($reservaResolvida->ehComum()) {
            return $this->comumAdminAction->handle(
                $reservaResolvida->registro,
                $request
            );
        }

        if ($reservaResolvida->ehMotoristaApp()) {
            $reservaMotoristaApp = $reservaResolvida->registro;

            $pagamento = $reservaMotoristaApp->pagamentos()
                ->whereRaw(
                    'LOWER(TRIM(status)) = ?',
                    ['pendente']
                )
                ->latest('id')
                ->first();

            if (! $pagamento) {
                $codigoReserva =
                    $reservaMotoristaApp->codigoReserva?->codigo
                    ?? (
                        'MAP-'.str_pad(
                            (string) $reservaMotoristaApp->id,
                            6,
                            '0',
                            STR_PAD_LEFT
                        )
                    );

                return redirect()
                    ->route('reserva.show', $codigoReserva)
                    ->with(
                        'warning',
                        'Não foi encontrado um lançamento pendente para esta reserva.'
                    );
            }

            return $this->motoristaAppAdminAction->handle(
                $reservaMotoristaApp,
                $request
            );
        }

        /*
         * Mantenha isto somente se algumas rotas realmente enviarem
         * diretamente o ID de ReservaMotoristaAppPagamento.
         */
        if (ctype_digit((string) $identificador)) {
            $pagamento = ReservaMotoristaAppPagamento::query()
                ->find((int) $identificador);

            if ($pagamento && $pagamento->reservaMotoristaApp) {
                return $this->motoristaAppAdminAction->handle(
                    $pagamento->reservaMotoristaApp,
                    $request
                );
            }
        }

        throw new \LogicException(
            'Tipo de reserva não suportado para confirmação de pagamento admin.'
        );
    }
}
