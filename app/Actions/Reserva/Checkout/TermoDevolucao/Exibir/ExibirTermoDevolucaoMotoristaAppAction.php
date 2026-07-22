<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Exibir;

use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinReservaComumAction;
use App\Models\ContratoCheckin;
use App\Models\ContratoCheckout;
use App\Models\Reserva;
use App\Models\ReservaMotoristaApp;
use App\Services\PdfService;

class ExibirTermoDevolucaoMotoristaAppAction
{
    public function __construct(
        private PdfService $pdfService,
        private ExibirContratoCheckinReservaComumAction $comumAction,
    ) {}

    public function servirPdf(ReservaMotoristaApp $reserva): \Illuminate\Http\Response
    {
        $reserva->loadMissing([
            'codigoReserva', 'empresa', 'user', 'veiculo.marca', 'veiculo.modelo',
            'planoMotoristaApp', 'solicitacao.planoMotoristaApp', 'pagamentos',
        ]);

        $contratoCheckin = $this->obterContratoCheckin($reserva);
        if (!$contratoCheckin) {
            throw new \RuntimeException('Contrato de check-in de motorista de aplicativo ainda nao foi gerado.');
        }

        $contratoCheckout = $this->obterContratoCheckout($reserva);
        $reservaAdaptada = $this->montarReservaAdaptada($reserva, $contratoCheckin, $contratoCheckout);
        $data = $this->comumAction->montarDados($reservaAdaptada);
        $checkoutDocs = is_array($reserva->documentos_json ?? null)
            ? data_get($reserva->documentos_json, 'checkout', [])
            : [];

        data_set($data, 'documento.titulo', 'TERMO DE DEVOLUCAO - MOTORISTA APP');
        data_set($data, 'documento.subtitulo', 'COMPROVANTE DE DEVOLUCAO DO VEICULO');
        data_set($data, 'documento.data_assinatura', data_get($checkoutDocs, 'data_assinatura') ?? $contratoCheckout?->data_assinatura);
        data_set($data, 'contrato.assinatura', data_get($checkoutDocs, 'assinatura') ?? $contratoCheckout?->assinatura);
        data_set($data, 'contrato.data_assinatura', data_get($checkoutDocs, 'data_assinatura') ?? $contratoCheckout?->data_assinatura);
        data_set($data, 'assinatura', data_get($checkoutDocs, 'assinatura') ?? $contratoCheckout?->assinatura ?? data_get($data, 'assinatura'));
        data_set($data, 'veiculo.quilometros', $reserva->quilometragem_final ?? $contratoCheckout?->quilometragem_devolucao ?? data_get($data, 'veiculo.quilometros'));
        data_set($data, 'veiculo.combustivel', $reserva->nivel_combustivel_final ?? $contratoCheckout?->nivel_combustivel_devolucao ?? data_get($data, 'veiculo.combustivel'));
        data_set($data, 'checkout.quilometragem_final', $reserva->quilometragem_final ?? $contratoCheckout?->quilometragem_devolucao);
        data_set($data, 'checkout.combustivel_final', $reserva->nivel_combustivel_final ?? $contratoCheckout?->nivel_combustivel_devolucao);
        data_set($data, 'checkout.data_hora_devolucao', $this->dataHoraDevolucao($reserva, $contratoCheckout));
        data_set($data, 'checkout.notas_devolucao', data_get($checkoutDocs, 'notas_devolucao') ?? $contratoCheckout?->notas_devolucao);
        data_set($data, 'checkout.observacoes_devolucao', data_get($checkoutDocs, 'observacoes_devolucao') ?? $contratoCheckout?->observacoes_devolucao);
        data_set($data, 'checkout.fotos', $this->fotosCheckout($checkoutDocs, $contratoCheckout));
        data_set($data, 'pagamentos', $this->pagamentosMotoristaApp($reserva));

        $totalFinanceiro = $this->totalFinanceiroMotoristaApp($reserva);
        data_set($data, 'valores.valor_total', $totalFinanceiro['valor']);
        data_set($data, 'faturacao.valor_total', $totalFinanceiro['formatado']);
        data_set($data, 'financeiro_detalhado.total', $totalFinanceiro['formatado']);
        data_set($data, 'financeiro_detalhado.subtotal', $totalFinanceiro['formatado']);

        $pdf = $this->pdfService->gerarConteudo(
            view: 'pdf.contratos.checkout.motorista-app',
            data: $data,
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="termo-devolucao-motorista-app-' . $reserva->id . '.pdf"',
        ]);
    }

    private function obterContratoCheckin(ReservaMotoristaApp $reserva): ?ContratoCheckin
    {
        $contratoId = (int) data_get($reserva->documentos_json, 'checkin.contrato_checkin_id', 0);
        $reservaOpId = (int) ($reserva->codigoReserva?->reserva_id ?? data_get($reserva->documentos_json, 'checkin.reserva_operacional_id', 0));

        if ($contratoId > 0) {
            $contrato = ContratoCheckin::with(['reserva', 'veiculo.marca', 'veiculo.modelo', 'marca', 'modelo'])->find($contratoId);
            if ($contrato) {
                return $contrato;
            }
        }

        return ContratoCheckin::with(['reserva', 'veiculo.marca', 'veiculo.modelo', 'marca', 'modelo'])
            ->where(function ($q) use ($reserva, $reservaOpId) {
                $q->where('reserva_id', (int) $reserva->id);
                if ($reservaOpId > 0) {
                    $q->orWhere('reserva_id', $reservaOpId);
                }
            })
            ->latest('id')
            ->first();
    }

    private function obterContratoCheckout(ReservaMotoristaApp $reserva): ?ContratoCheckout
    {
        $reservaOpId = (int) ($reserva->codigoReserva?->reserva_id ?? data_get($reserva->documentos_json, 'checkin.reserva_operacional_id', 0));

        return ContratoCheckout::with(['reserva', 'veiculo.marca', 'veiculo.modelo'])
            ->where(function ($q) use ($reserva, $reservaOpId) {
                $q->where('reserva_id', (int) $reserva->id);
                if ($reservaOpId > 0) {
                    $q->orWhere('reserva_id', $reservaOpId);
                }
            })
            ->latest('id')
            ->first();
    }

    private function montarReservaAdaptada(
        ReservaMotoristaApp $reserva,
        ContratoCheckin $contratoCheckin,
        ?ContratoCheckout $contratoCheckout,
    ): Reserva {
        $reservaAdaptada = $contratoCheckin->reserva ?: new Reserva();
        $reservaAdaptada->id = $reservaAdaptada->id ?: $reserva->id;
        $reservaAdaptada->empresa_id = $reserva->empresa_id;
        $reservaAdaptada->user_id = $reserva->user_id;
        $reservaAdaptada->veiculo_id = $contratoCheckout?->veiculo_id ?? $contratoCheckin->veiculo_id ?? $reserva->veiculo_id;
        $reservaAdaptada->estacao_retirada_id = $reserva->estacao_retirada_id;
        $reservaAdaptada->data_inicio = $reserva->data_inicio;
        $reservaAdaptada->data_fim = $reserva->data_fim;
        $reservaAdaptada->quantidade_dias = $reserva->quantidade_dias;
        $reservaAdaptada->quilometragem_final = $reserva->quilometragem_final ?? $contratoCheckout?->quilometragem_devolucao;
        $reservaAdaptada->nivel_combustivel_final = $reserva->nivel_combustivel_final ?? $contratoCheckout?->nivel_combustivel_devolucao;
        $reservaAdaptada->setRelation('contratoCheckin', $contratoCheckin);

        if ($contratoCheckout) {
            $reservaAdaptada->setRelation('contratoCheckout', $contratoCheckout);
        }

        $reservaAdaptada->setRelation('empresa', $reserva->empresa);
        $reservaAdaptada->setRelation('usuario', $reserva->user);
        $reservaAdaptada->setRelation('veiculo', $contratoCheckout?->veiculo ?? $contratoCheckin->veiculo ?? $reserva->veiculo);

        return $reservaAdaptada;
    }

    private function dataHoraDevolucao(ReservaMotoristaApp $reserva, ?ContratoCheckout $contratoCheckout): ?string
    {
        if ($reserva->checkout_realizado_em) {
            return \Carbon\Carbon::parse($reserva->checkout_realizado_em)->format('d/m/Y H:i');
        }

        if ($contratoCheckout?->data_devolucao) {
            return trim(
                \Carbon\Carbon::parse($contratoCheckout->data_devolucao)->format('d/m/Y') . ' ' .
                ($contratoCheckout->hora_devolucao ? substr((string) $contratoCheckout->hora_devolucao, 0, 5) : '')
            );
        }

        return null;
    }

    private function fotosCheckout(array $checkoutDocs, ?ContratoCheckout $contratoCheckout): array
    {
        $map = [
            'Frente' => data_get($checkoutDocs, 'fotos.foto_frente_co') ?? data_get($checkoutDocs, 'fotos.foto_frente_path') ?? $contratoCheckout?->foto_frente_path,
            'Tras' => data_get($checkoutDocs, 'fotos.foto_tras_co') ?? data_get($checkoutDocs, 'fotos.foto_tras_path') ?? $contratoCheckout?->foto_tras_path,
            'Direita' => data_get($checkoutDocs, 'fotos.foto_direita_co') ?? data_get($checkoutDocs, 'fotos.foto_direita_path') ?? $contratoCheckout?->foto_direita_path,
            'Esquerda' => data_get($checkoutDocs, 'fotos.foto_esquerda_co') ?? data_get($checkoutDocs, 'fotos.foto_esquerda_path') ?? $contratoCheckout?->foto_esquerda_path,
            'Velocimetro' => data_get($checkoutDocs, 'fotos.foto_velocimetro_co') ?? data_get($checkoutDocs, 'fotos.foto_velocimetro_path') ?? $contratoCheckout?->foto_velocimetro_path,
            'Combustivel' => data_get($checkoutDocs, 'fotos.foto_combustivel_co') ?? data_get($checkoutDocs, 'fotos.foto_combustivel_path') ?? $contratoCheckout?->foto_combustivel_path,
        ];

        return collect($map)
            ->filter()
            ->map(fn ($path, $label) => [
                'label' => $label,
                'url' => asset('storage/' . ltrim((string) $path, '/')),
            ])
            ->values()
            ->all();
    }

    private function pagamentosMotoristaApp(ReservaMotoristaApp $reserva): array
    {
        return $reserva->pagamentos
            ->sortByDesc('id')
            ->map(function ($pagamento) {
                $valor = (float) ($pagamento->valor_total ?? $pagamento->valor_unitario ?? 0);

                return [
                    'data' => $pagamento->pago_em
                        ? \Carbon\Carbon::parse($pagamento->pago_em)->format('d/m/Y H:i')
                        : ($pagamento->created_at ? \Carbon\Carbon::parse($pagamento->created_at)->format('d/m/Y H:i') : '-'),
                    'valor' => $this->money($valor),
                ];
            })
            ->values()
            ->all();
    }

    private function totalFinanceiroMotoristaApp(ReservaMotoristaApp $reserva): array
    {
        $valor = (float) (
            $reserva->valor_total_final
            ?: ((float) ($reserva->valor_periodo_contrato ?: $reserva->valor_plano) + (float) $reserva->valor_opcionais)
        );

        if ($valor <= 0) {
            $valor = (float) $reserva->pagamentos->sum(fn ($pagamento) => (float) ($pagamento->valor_total ?? $pagamento->valor_unitario ?? 0));
        }

        return [
            'valor' => $valor,
            'formatado' => $this->money($valor),
        ];
    }

    private function money(float $valor): string
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }
}
