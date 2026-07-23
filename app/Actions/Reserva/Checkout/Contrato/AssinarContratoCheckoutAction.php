<?php

namespace App\Actions\Reserva\Checkout\Contrato;

use Illuminate\Http\Request;
use App\Services\Reserva\ReservaResolver;
use App\Services\BreadcrumbService;
use App\DTO\BreadCrumbItem;

class AssinarContratoCheckoutAction
{
    public function __construct(protected BreadcrumbService $breadcrumbService) {}

    public function handle(Request $request, $codigo, ReservaResolver $reservaResolver)
    {
        try {
            $reservaResolvida = $reservaResolver->resolver($codigo);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.index')
                ->with('error', $e->getMessage());
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº ' . $codigo, route('reserva.show', $codigo)),
            new BreadCrumbItem('Assinar Contrato de Check-out'),
        ]);

        if ($reservaResolvida->ehMotoristaApp()) {
            /** @var \App\Models\ReservaMotoristaApp $reg */
            $reg = $reservaResolvida->registro;
            $reg->loadMissing(['user', 'empresa', 'veiculo.marca', 'veiculo.modelo', 'planoMotoristaApp', 'solicitacao.planoMotoristaApp', 'estacaoRetirada', 'pagamentos']);

            $plano = $reg->planoMotoristaApp ?? $reg->solicitacao?->planoMotoristaApp;
            $valorPeriodoAtual = (float) (
                $plano?->valor_periodo
                ?? $reg->solicitacao?->valor_periodo
                ?? $reg->pagamentos?->first()?->valor_unitario
                ?? $reg->pagamentos?->first()?->valor_total
                ?? 0
            );
            $tarifaValor = (float) (
                $reg->valor_periodo_contrato
                ?: ($reg->valor_plano
                ?: $valorPeriodoAtual)
            );
            $valorOpcionais = (float) ($reg->valor_opcionais ?? 0);
            $valorTotal = round($tarifaValor + $valorOpcionais, 2);

            return view('reserva.assinar-contrato-checkout', [
                'reserva' => $reg,
                'breadcrumb' => $breadcrumb,
                'contextoAssinatura' => [
                    'empresa' => $reg->empresa,
                    'usuario' => $reg->user,
                    'codigo' => $codigo,
                    'veiculo' => $reg->veiculo,
                    'data_inicio' => $reg->data_inicio,
                    'data_fim' => $reg->data_fim,
                    'quantidade_dias' => $reg->quantidade_dias,
                    'tarifa_label' => 'Valor do Ciclo (' . ($plano?->nome ?? 'Plano') . ')',
                    'tarifa_valor' => $tarifaValor,
                    'valor_opcionais' => $valorOpcionais,
                    'valor_total' => $valorTotal,
                    'tipo' => 'MAP',
                    'plano_nome' => $plano?->nome ?? 'Plano Motorista App',
                ],
                'titulo' => 'Assinar Contrato de Check-out — Reserva Nº ' . $codigo,
            ]);
        }

        /** @var \App\Models\Reserva $reserva */
        $reserva = $reservaResolvida->registro;
        $reserva->loadMissing(['usuario', 'empresa', 'veiculo.marca', 'veiculo.modelo', 'contratoCheckout', 'estacaoRetirada', 'estacaoDevolucao']);

        if (!$reserva->contratoCheckout) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('error', 'Check-out não encontrado para esta reserva.');
        }

        return view('reserva.assinar-contrato-checkout', [
            'reserva' => $reserva,
            'breadcrumb' => $breadcrumb,
            'contextoAssinatura' => [
                'empresa' => $reserva->empresa,
                'usuario' => $reserva->usuario,
                'codigo' => $codigo,
                'veiculo' => $reserva->contratoCheckout?->veiculo ?? $reserva->veiculo,
                'data_inicio' => $reserva->data_inicio,
                'data_fim' => $reserva->data_fim,
                'quantidade_dias' => $reserva->quantidade_dias,
                'tarifa_label' => 'Tarifa diária',
                'tarifa_valor' => (float) ($reserva->tarifa_base ?? 0),
                'taxa_entrega' => (float) ($reserva->taxa_entrega ?? 0),
                'taxa_coleta' => (float) ($reserva->taxa_coleta ?? 0),
                'valor_total' => (float) ($reserva->valor_total_final ?? 0),
                'tipo' => 'RES',
                'estacao_retirada' => $reserva->estacaoRetirada?->nome ?? '-',
                'estacao_devolucao' => $reserva->estacaoDevolucao?->nome ?? '-',
            ],
            'titulo' => 'Assinar Contrato de Check-out — Reserva Nº ' . $codigo,
        ]);
    }
}
