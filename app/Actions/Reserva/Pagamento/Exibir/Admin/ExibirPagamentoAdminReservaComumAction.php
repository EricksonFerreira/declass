<?php

namespace App\Actions\Reserva\Pagamento\Exibir\Admin;

use App\Actions\Reserva\Pagamento\ExibirFormularioPagamentoAction;
use App\DTO\BreadCrumbItem;
use App\Models\Reserva;
use App\Services\BreadcrumbService;

class ExibirPagamentoAdminReservaComumAction
{
    public function __construct(
        private ExibirFormularioPagamentoAction $exibirFormularioPagamentoAction,
        private BreadcrumbService $breadcrumbService,
    ) {}

    public function handle(Reserva $reserva): mixed
    {
        $reserva->loadMissing([
            'codigoReserva',
            'opcionais',
            'cupomDesconto',
            'estacaoRetirada',
            'estacaoDevolucao',
            'grupoFrota',
            'modelos',
            'transacoes',
        ]);

        $codigo = $reserva->codigoReserva?->codigo ?? ('RES-'.str_pad((string) $reserva->id, 6, '0', STR_PAD_LEFT));

        $data = $this->exibirFormularioPagamentoAction->handle($reserva);

        if (($data['saldoPendente'] ?? 0) <= 0.009) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('success', 'Esta reserva já se encontra com o pagamento totalmente quitado.');
        }

        $titulo = 'Efetuar Pagamento da Reserva';

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Reserva Nº '.$codigo, route('reserva.show', $codigo)),
            new BreadCrumbItem('Efetuar Pagamento'),
        ]);

        return view('reserva.efetuar-pagamento.efetuar-pagamento', [
            'reserva' => $reserva,
            'breadcrumb' => $breadcrumb,
            'titulo' => $titulo,
            ...$data,
        ]);
    }
}
