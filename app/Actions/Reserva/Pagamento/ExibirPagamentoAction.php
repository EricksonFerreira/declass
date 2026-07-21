<?php

namespace App\Actions\Reserva\Pagamento;

use App\Actions\Reserva\Pagamento\Exibir\Admin\ExibirPagamentoMotoristaAppAdminAction;
use App\Actions\Reserva\Pagamento\Exibir\Admin\ExibirPagamentoReservaComumAdminAction;
use App\Actions\Reserva\Pagamento\Exibir\Cliente\ExibirPagamentoMotoristaAppClienteAction;
use App\Actions\Reserva\Pagamento\Exibir\Cliente\ExibirPagamentoReservaComumClienteAction;
use App\Enums\TipoReservaEnum;
use App\Services\Reserva\ReservaResolver;

class ExibirPagamentoAction
{
    public function __construct(
        private ReservaResolver $reservaResolver,
        private ExibirPagamentoReservaComumAdminAction $comumAdmin,
        private ExibirPagamentoReservaComumClienteAction $comumCliente,
        private ExibirPagamentoMotoristaAppAdminAction $motoristaAppAdmin,
        private ExibirPagamentoMotoristaAppClienteAction $motoristaAppCliente,
    ) {}

    public function handle(string $codigo, bool $admin = true): mixed
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        if ($reservaResolvida->ehComum()) {
            return $admin
                ? $this->comumAdmin->handle($codigo)
                : $this->comumCliente->handle($reservaResolvida->id());
        }

        if ($reservaResolvida->ehMotoristaApp()) {
            $reserva = $reservaResolvida->reservaMotoristaApp();
            $reserva->load('pagamentos');
            $pagamento = $reserva->pagamentos
                ->filter(fn ($item) => strtolower((string) ($item->status ?? '')) === 'pendente')
                ->sortByDesc('id')
                ->first();

            if (! $pagamento) {
                return redirect()
                    ->route($admin ? 'reserva.show' : 'reserva.minhas.show', $reservaResolvida->codigo)
                    ->with('success', 'Nao ha pagamento pendente para esta reserva.');
            }

            return $admin
                ? $this->motoristaAppAdmin->handle((int) $pagamento->id)
                : $this->motoristaAppCliente->handle((int) $pagamento->id);
        }

        throw new \LogicException('Tipo de reserva nao suportado para exibicao do pagamento.');
    }

    public function handlePorPagamento(int $pagamentoId, TipoReservaEnum $tipo, bool $admin = true): mixed
    {
        return $tipo === TipoReservaEnum::MOTORISTA_APP
            ? ($admin ? $this->motoristaAppAdmin->handle($pagamentoId) : $this->motoristaAppCliente->handle($pagamentoId))
            : throw new \LogicException('Apenas reservas Motorista APP possuem pagamento por ID.');
    }
}
