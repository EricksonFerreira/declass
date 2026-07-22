<?php

namespace App\Http\Controllers\Reserva;

use App\Actions\Reserva\Checkout\Dano\Adicionar\AdicionarDanoVeiculoAction;
use App\Actions\Reserva\Checkout\Exibir\ExibirCheckoutAction;
use App\Actions\Reserva\Checkout\Realizar\RealizarCheckoutAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reserva\AdicionarDanoVeiculoRequest;
use App\Http\Requests\Reserva\FinalizarCheckoutVistoriaRequest;
use App\Services\BreadcrumbService;
use App\Services\CategoriaDanoService;
use App\Services\ReservaService;
use Illuminate\Http\Request;

class ReservaCheckoutController extends Controller
{
    use ReservaHelpersTrait;

    public function __construct(
        protected ReservaService $reservaService,
        protected BreadcrumbService $breadcrumbService,
        protected CategoriaDanoService $categoriaDanoService,
        protected ExibirCheckoutAction $exibirCheckoutAction,
        protected RealizarCheckoutAction $realizarCheckoutAction,
        protected AdicionarDanoVeiculoAction $adicionarDanoVeiculoAction
    ) {}

    public function checkoutVistoria(Request $request, $codigo)
    {
        try {
            return $this->exibirCheckoutAction->handle($codigo, $request);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('error', $e->getMessage());
        }
    }

    public function finalizarCheckoutVistoria(FinalizarCheckoutVistoriaRequest $request, $codigo)
    {
        try {
            $this->realizarCheckoutAction->handle($codigo, $request->validated(), $request);

            return response()->json([
                'success' => true,
                'message' => 'Check-out concluído e reserva finalizada.',
                'redirect' => route('reserva.show', $codigo),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar check-out: '.$e->getMessage(),
            ], 422);
        }
    }

    public function adicionarDanoVeiculo(AdicionarDanoVeiculoRequest $request, $codigo)
    {
        try {
            $dano = $this->adicionarDanoVeiculoAction->handle($request->validated(), $codigo, $request);

            return response()->json($dano);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Erro ao salvar dano: '.$e->getMessage()], 500);
        }
    }

    public function assinarContratoCheckout(Request $request, $codigo, \App\Services\Reserva\ReservaResolver $reservaResolver)
    {
        try {
            $reservaResolvida = $reservaResolver->resolver($codigo);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.index')
                ->with('error', $e->getMessage());
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new \App\DTO\BreadCrumbItem('Home', route('home')),
            new \App\DTO\BreadCrumbItem('Reservas', route('reserva.index')),
            new \App\DTO\BreadCrumbItem('Detalhes da Reserva Nº ' . $codigo, route('reserva.show', $codigo)),
            new \App\DTO\BreadCrumbItem('Assinar Contrato de Check-out'),
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

    public function salvarAssinarContratoCheckout(Request $request, $codigo, \App\Services\Reserva\ReservaResolver $reservaResolver)
    {
        $validated = $request->validate([
            'assinatura' => ['required', 'string']
        ]);

        try {
            $reservaResolvida = $reservaResolver->resolver($codigo);
            if ($reservaResolvida->ehMotoristaApp()) {
                /** @var \App\Models\ReservaMotoristaApp $reg */
                $reg = $reservaResolvida->registro;
                $docs = is_array($reg->documentos_json) ? $reg->documentos_json : [];
                $checkout = isset($docs['checkout']) && is_array($docs['checkout']) ? $docs['checkout'] : [];
                
                $checkout['assinatura'] = $validated['assinatura'];
                $checkout['data_assinatura'] = now()->toDateTimeString();
                
                $docs['checkout'] = $checkout;
                $reg->documentos_json = $docs;
                $reg->save();

                return response()->json(['success' => true]);
            }

            /** @var \App\Models\Reserva $reserva */
            $reserva = $reservaResolvida->registro;
            $co = $reserva->contratoCheckout;
            if (!$co) {
                return response()->json(['error' => 'Check-out não registrado.'], 404);
            }

            $base64 = $validated['assinatura'];
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $base64 = substr($base64, strpos($base64, ',') + 1);
                $base64 = base64_decode($base64);
                
                $dir = 'checkout/reserva_' . (int) $reserva->id;
                $filename = 'assinatura_' . \Illuminate\Support\Str::random(10) . '.png';
                $relPath = $dir . '/' . $filename;
                
                \Illuminate\Support\Facades\Storage::disk('public')->put($relPath, $base64);
                
                $co->update([
                    'assinatura' => $relPath,
                    'data_assinatura' => now(),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Falha ao salvar assinatura: ' . $e->getMessage()], 500);
        }
    }

    public function verContratoCheckout(Request $request, $codigo, \App\Services\Reserva\ReservaResolver $reservaResolver)
    {
        try {
            $reservaResolvida = $reservaResolver->resolver($codigo);

            // Exibir/Gerar PDF de devolução
            $exibirAction = app(\App\Actions\Reserva\Checkout\TermoDevolucao\Exibir\ExibirTermoDevolucaoAction::class);
            return $exibirAction->handle($codigo);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reserva.index')
                ->with('error', $e->getMessage());
        }
    }
}
