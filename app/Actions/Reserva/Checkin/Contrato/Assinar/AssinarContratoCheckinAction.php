<?php

namespace App\Actions\Reserva\Checkin\Contrato\Assinar;

use Illuminate\Http\Request;
use App\Services\Reserva\ReservaResolver;
use App\Services\BreadcrumbService;
use App\DTO\BreadCrumbItem;

class AssinarContratoCheckinAction
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
            new BreadCrumbItem('Assinar Contrato de Check-in'),
        ]);

        if ($reservaResolvida->ehMotoristaApp()) {
            /** @var \App\Models\ReservaMotoristaApp $reg */
            $reg = $reservaResolvida->registro;
            $reg->loadMissing(['user', 'empresa', 'veiculo.marca', 'veiculo.modelo', 'planoMotoristaApp', 'solicitacao.planoMotoristaApp', 'estacaoRetirada', 'pagamentos']);

            $checkinDocs = is_array($reg->documentos_json ?? null)
                ? data_get($reg->documentos_json, 'checkin', [])
                : [];
            $reservaOperacionalCheckinId = (int) data_get($checkinDocs, 'reserva_operacional_id', 0);
            $contratoCheckin = null;

            if ((int) data_get($checkinDocs, 'contrato_checkin_id', 0) > 0) {
                $contratoCheckin = \App\Models\ContratoCheckin::find((int) data_get($checkinDocs, 'contrato_checkin_id'));
            }

            if (! $contratoCheckin && $reservaOperacionalCheckinId > 0) {
                $contratoCheckin = \App\Models\ContratoCheckin::where('reserva_id', $reservaOperacionalCheckinId)
                    ->latest('id')
                    ->first();
            }

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
            $opcionaisSelecionados = is_array($reg->opcionais_json ?? null) ? $reg->opcionais_json : [];
            $opcionaisMap = $plano?->opcionais?->keyBy('id') ?? collect();
            $opcionaisList = collect($opcionaisSelecionados)
                ->map(function ($item) use ($opcionaisMap) {
                    $opcionalId = (int) ($item['id'] ?? 0);
                    $opcional = $opcionaisMap->get($opcionalId);
                    $quantidade = max(1, (int) ($item['quantidade'] ?? 1));
                    $subtotal = (float) (
                        $item['preco_total']
                        ?? $opcional?->pivot?->preco_total
                        ?? ($opcional?->preco_integral ?? $opcional?->preco_por_dia ?? 0)
                    );

                    return [
                        'nome' => $opcional?->nome ?? ($item['nome'] ?? ('Opcional #' . $opcionalId)),
                        'quantidade' => $quantidade,
                        'subtotal' => $subtotal,
                    ];
                })
                ->filter(fn($item) => $item['subtotal'] > 0 || !empty($item['nome']))
                ->values()
                ->toArray();

            return view('reserva.assinar-contrato-checkin', [
                'reserva' => $reg,
                'contratoCheckin' => $contratoCheckin,
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
                    'opcionais_lista' => $opcionaisList,
                    'valor_total' => $valorTotal,
                    'tipo' => 'MAP',
                    'plano_nome' => $plano?->nome ?? 'Plano Motorista App',
                    'estacao_retirada' => $reg->estacaoRetirada?->nome ?? '-',
                    'estacao_devolucao' => $reg->estacaoRetirada?->nome ?? '-',
                    'caucao' => (float) ($plano?->calcao ?? 0),
                ],
                'titulo' => 'Assinar Contrato de Check-in — Reserva Nº ' . $codigo,
            ]);
        }

        /** @var \App\Models\Reserva $reserva */
        $reserva = $reservaResolvida->registro;
        $reserva->loadMissing(['usuario', 'empresa', 'veiculo.marca', 'veiculo.modelo', 'contratoCheckin', 'estacaoRetirada', 'estacaoDevolucao', 'calcao', 'calcao.configuracao']);

        if (!$reserva->contratoCheckin) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('error', 'Contrato de check-in não encontrado.');
        }

        $calcaoRecord = $reserva->calcao;
        $valorCalculado = 0.0;
        if ($calcaoRecord) {
            $valorCalculado = (float) (
                $calcaoRecord->valor_capturado
                ?? $calcaoRecord->valor_autorizado
                ?? $calcaoRecord->valor_calculado
                ?? 0
            );
        } else {
            try {
                $calcaoService = app(\App\Services\CalcaoService::class);
                $calculo = $calcaoService->calcularValor($reserva);
                $valorCalculado = (float) ($calculo['valor_calculado'] ?? 0);
            } catch (\Throwable $e) {
                // fallback silencioso
            }
        }

        return view('reserva.assinar-contrato-checkin', [
            'reserva' => $reserva,
            'breadcrumb' => $breadcrumb,
            'contextoAssinatura' => [
                'empresa' => $reserva->empresa,
                'usuario' => $reserva->usuario,
                'codigo' => $codigo,
                'veiculo' => $reserva->contratoCheckin?->veiculo ?? $reserva->veiculo,
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
                'caucao' => $valorCalculado,
            ],
            'titulo' => 'Assinar Contrato de Check-in — Reserva Nº ' . $codigo,
        ]);
    }
}
