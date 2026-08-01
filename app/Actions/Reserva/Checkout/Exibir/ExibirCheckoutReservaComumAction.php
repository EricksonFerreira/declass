<?php

namespace App\Actions\Reserva\Checkout\Exibir;

use App\DTO\BreadCrumbItem;
use App\DTO\Reserva\ReservaResolvida;
use App\Models\Calcao;
use App\Models\ContratoCheckout;
use App\Services\BreadcrumbService;
use App\Services\CalcaoService;
use App\Services\CategoriaDanoService;
use Illuminate\Http\Request;

class ExibirCheckoutReservaComumAction
{
    public function __construct(
        private BreadcrumbService $breadcrumbService,
        private CategoriaDanoService $categoriaDanoService,
        private CalcaoService $calcaoService,
    ) {}

    public function handle(ReservaResolvida $reserva, Request $request): mixed
    {
        $registro = $reserva->registro;
        $codigo = $reserva->codigo;

        if (! $registro->isPagamentoEfetuado()) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('warning', 'O pagamento deve ser efetuado antes de realizar o check-out.');
        }

        if (! $registro->contratoCheckin || $registro->contratoCheckin->assinado !== 'S') {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('warning', 'O check-in deve ser realizado e o contrato assinado antes do check-out.');
        }

        if (! $registro->podeFazerCheckout()) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('warning', 'Esta reserva nao esta mais em status valido para check-out.');
        }

        if (ContratoCheckout::where('reserva_id', (int) $registro->id)->exists()) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('warning', 'Check-out desta reserva ja foi registrado.');
        }

        if ($mensagemCalcao = $this->validarCalcaoParaCheckout($registro)) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('error', $mensagemCalcao);
        }

        if (! $registro->veiculo_id) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('error', 'Associe um veiculo a reserva antes do check-out.');
        }

        $categoriaDanos = $this->categoriaDanoService->getAllForSelect();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº'.$codigo, route('reserva.show', $codigo)),
            new BreadCrumbItem('Check-out / Vistoria'),
        ]);

        return view('reserva.checkout', compact('registro', 'categoriaDanos', 'breadcrumb'))
            ->with('titulo', 'Check-out (vistoria) — reserva Nº '.$registro->id)
            ->with('reserva', $registro);
    }

    private function validarCalcaoParaCheckout($registro): ?string
    {
        $calculo = $this->calcaoService->calcularValor($registro);
        $valorCalculado = (float) ($calculo['valor_calculado'] ?? 0);

        if ($valorCalculado <= 0) {
            return null;
        }

        $calcao = $registro->calcao;
        if (! $calcao) {
            return 'Para fazer o checkout e obrigatorio registrar o pagamento do calcao primeiro.';
        }

        $valorCapturado = (float) ($calcao->valor_capturado ?? 0);
        $valorAutorizado = (float) ($calcao->valor_autorizado ?? 0);
        $valorBaseLiberacao = max($valorCalculado, $valorAutorizado);
        $valorCapturaOK = $valorCapturado + 0.01 >= $valorBaseLiberacao;

        if (! $valorCapturaOK) {
            return 'Para fazer o checkout e obrigatorio que o calção esteja totalmente pago e capturado antes. Status atual: '.(string) ($calcao->label_status ?? $calcao->status).'. Valor capturado: R$ '.number_format($valorCapturado, 2, ',', '.').'. Valor exigido: R$ '.number_format($valorBaseLiberacao, 2, ',', '.');
        }

        return null;
    }
}
