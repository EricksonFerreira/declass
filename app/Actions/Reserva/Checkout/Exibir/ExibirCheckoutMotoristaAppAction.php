<?php

namespace App\Actions\Reserva\Checkout\Exibir;

use App\DTO\BreadCrumbItem;
use App\DTO\Reserva\ReservaResolvida;
use App\Services\BreadcrumbService;
use DB;
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

        $contratoId = (int) data_get($registro->documentos_json, 'checkin.contrato_checkin_id', 0);
        $contratoCheckin = null;
        if ($contratoId > 0) {
            $contratoCheckin = DB::table('contrato_checkin')->find($contratoId);
        }

        if (! $contratoCheckin || empty($contratoCheckin->assinatura)) {
            return redirect()->route('reserva.show', $codigo)
                ->with('error', 'Assine o contrato de check-in antes de fazer checkout.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Reserva motorista APP #'.$registro->id, route('reserva.show', $codigo)),
            new BreadCrumbItem('Check-out'),
        ]);

        $categoriaDanos = app(\App\Services\CategoriaDanoService::class)->getAllForSelect();

        return view('reserva.checkout-motorista-app', [
            'registro' => $registro,
            'reserva' => $registro,
            'categoriaDanos' => $categoriaDanos,
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', 'Check-out da reserva motorista APP #'.$registro->id);
    }
}
