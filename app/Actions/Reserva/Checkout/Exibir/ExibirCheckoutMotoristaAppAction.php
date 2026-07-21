<?php

namespace App\Actions\Reserva\Checkout\Exibir;

use App\DTO\BreadCrumbItem;
use App\DTO\Reserva\ReservaResolvida;
use App\Services\BreadcrumbService;
use Illuminate\Http\Request;

class ExibirCheckoutMotoristaAppAction
{
    public function __construct(
        private BreadcrumbService $breadcrumbService,
    ) {}

    public function handle(ReservaResolvida $reserva, Request $request): mixed
    {
        $registro = $reserva->registro;
        $codigo = $reserva->codigo;

        if (empty($registro->checkin_realizado_em)) {
            return redirect()->route('reserva.show', $codigo)
                ->with('error', 'O check-in precisa ser registrado antes do checkout.');
        }

        if (empty(data_get($registro->contrato_snapshot_json, 'assinatura'))) {
            return redirect()->route('reserva.show', $codigo)
                ->with('error', 'Assine o contrato de check-in antes de fazer checkout.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Reserva motorista APP #' . $registro->id, route('reserva.show', $codigo)),
            new BreadCrumbItem('Check-out'),
        ]);

        return view('reserva.checkout-motorista-app', compact('registro', 'breadcrumb'))
            ->with('titulo', 'Check-out da reserva motorista APP #' . $registro->id);
    }
}
