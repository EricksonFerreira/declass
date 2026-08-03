<?php

namespace App\Actions\Reserva\Exibir;

use App\DTO\BreadCrumbItem;
use App\DTO\Reserva\ReservaResolvida;
use App\Models\ConfiguracaoCalcao;
use App\Models\Reserva;
use App\Services\BreadcrumbService;
use Carbon\Carbon;

class ExibirReservaComumAction
{
    public function __construct(
        private readonly BreadcrumbService $breadcrumbService,
    ) {}

    public function handle(ReservaResolvida $reserva): mixed
    {
        $registro = $reserva->registro;
        $registro->load([
            'empresa', 'criadoPor', 'nfseAtual', 'estacaoRetirada', 'estacaoDevolucao',
            'grupoFrota', 'cupomDesconto', 'opcionais',
            'transacoes' => fn ($q) => $q->with(['user', 'pagoPor'])->orderByDesc('id'),
            'contratoCheckin.grupoFrotaOriginal', 'contratoCheckin.veiculo.marca',
            'contratoCheckin.usuarioUpgrade', 'contratoCheckout', 'calcao',
            'veiculoDanos' => fn ($q) => $q->with('imagem')->orderByDesc('id')->take(10),
            'avariaCobrancas' => fn ($q) => $q->orderByDesc('id')->take(10),
            'multas' => fn ($q) => $q->orderByDesc('id')->take(5),
        ]);

        $calcaoConfiguradoEmpresa = false;
        if ($registro->empresa_id) {
            $calcaoConfiguradoEmpresa = ConfiguracaoCalcao::query()
                ->where('empresa_id', $registro->empresa_id)
                ->whereNull('grupo_frota_id')
                ->whereNull('modelo_id')
                ->where('ativo', true)
                ->exists();
        }

        $dados = $this->montarDadosDaVisao($registro);
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva NÂº' . $reserva->codigo),
        ]);

        return view('reserva.show', [
            'reserva' => $registro,
            'codigo' => $reserva->codigo,
            'breadcrumb' => $breadcrumb,
            'titulo' => 'Detalhes da Reserva',
            'calcaoConfiguradoEmpresa' => $calcaoConfiguradoEmpresa,
            ...$dados,
        ]);
    }

    private function montarDadosDaVisao(Reserva $reserva): array
    {
        $diasReserva = (int) ($reserva->quantidade_dias ?? 0);
        $tarifaBase = (float) ($reserva->tarifa_base ?? 0);
        $valorTarifaBase = $tarifaBase * $diasReserva;

        $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);
        if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
            $valorOpcionais = (float) $reserva->opcionais->sum(function ($opcional) {
                return (float) ($opcional->pivot->preco_total ?? 0);
            });
        }

        $percentualDesconto = (float) ($reserva->percentual_desconto ?? 0);
        $taxaColeta = (float) ($reserva->taxa_coleta ?? 0);
        $taxaEntrega = (float) ($reserva->taxa_entrega ?? 0);
        $subtotal = $valorTarifaBase + $valorOpcionais + $taxaColeta + $taxaEntrega;
        $valorDesconto = $subtotal * ($percentualDesconto / 100);
        $valorTotal = max(0, $subtotal - $valorDesconto);

        $statusReservaNome = $reserva->statusReserva->nome ?? 'Status';
        $statusReservaSlug = strtolower((string) $statusReservaNome);
        $statusReservaClass = match ($statusReservaSlug) {
            'confirmada' => 'is-success',
            'cancelada' => 'is-danger',
            'criada', 'pendente' => 'is-warning',
            default => 'is-neutral',
        };
        $statusIcon = match ($statusReservaSlug) {
            'confirmada' => 'fas fa-check-circle',
            'cancelada' => 'fas fa-times-circle',
            'criada', 'pendente' => 'fas fa-clock',
            default => 'fas fa-info-circle',
        };

        $nfseAtual = $reserva->nfseAtual ?? null;
        $reservaConfirmada = isset($reserva->status_reserva_id) && (int) $reserva->status_reserva_id === 2;
        $nfseNaoEmitida = ! $nfseAtual || ($nfseAtual->status ?? '') !== 'emitida';
        $nfseStatusLabel = $nfseAtual ? ucfirst($nfseAtual->status ?? 'pendente') : 'Não emitida';
        $nfseMotivo = $nfseAtual ? (app(\App\Services\NfseService::class)->mensagemErro($nfseAtual) ?? null) : null;
        $nfseErroDetalhado = null;
        if ($nfseAtual && ($nfseAtual->status ?? '') === 'erro' && is_array($nfseAtual->response ?? null)) {
            $nfseErroDetalhado = $nfseAtual->response['message'] ?? $nfseAtual->response['error'] ?? $nfseAtual->response['erro'] ?? null;
            if (! $nfseErroDetalhado && ! empty($nfseAtual->response['errors'])) {
                $nfseErroDetalhado = implode(' ', (array) $nfseAtual->response['errors']);
            }
        }

        $transacoesReserva = $reserva->transacoes ?? collect();
        $statusPagamentoAprovado = ['succeeded', 'approved', 'paid', 'completed', 'complete'];
        $statusPagamentoPendente = ['pending', 'processing', 'requires_action', 'requires_payment_method'];
        $statusPagamentoEstornado = ['refunded', 'refund', 'estorno', 'cancelled', 'canceled', 'cancelado', 'cancelada', 'reembolsado', 'reembolsada'];
        $transacoesPagamento = $transacoesReserva->filter(fn ($item) => (float) ($item->valor ?? 0) >= 0);
        $transacoesEstorno = $transacoesReserva->filter(fn ($item) =>
            (float) ($item->valor ?? 0) < 0 || in_array(strtolower((string) ($item->status ?? '')), $statusPagamentoEstornado, true));
        $transacoesPendentes = $transacoesReserva->filter(fn ($item) =>
            in_array(strtolower((string) ($item->status ?? '')), $statusPagamentoPendente, true));
        $totalPagamentos = (float) $transacoesPagamento->sum('valor');
        $totalEstornos = abs((float) $transacoesEstorno->sum('valor'));
        $totalPagoAprovado = (float) $transacoesReserva
            ->filter(fn ($item) => (float) ($item->valor ?? 0) > 0
                && in_array(strtolower((string) ($item->status ?? '')), $statusPagamentoAprovado, true))
            ->sum('valor');
        $pagamentoEfetuado = $reserva->isPagamentoEfetuado();
        if ($pagamentoEfetuado && $totalPagoAprovado < $valorTotal) {
            $totalPagoAprovado = $valorTotal;
        }
        $saldoPendenteAssinatura = max(0, $valorTotal - $totalPagoAprovado);
        $pagamentoEmDiaAssinatura = $saldoPendenteAssinatura <= 0.009;

        $sinistros = $reserva->sinistros ?? collect();
        $totalSinistros = $sinistros->count();
        $danosReserva = $reserva->veiculoDanos ?? collect();
        $danosPendentes = $danosReserva->whereNull('correcao_data');
        $totalDano = $danosReserva->count();
        $totalMultas = $reserva->multas?->count() ?? 0;
        $totalCobrancasAvaria = $reserva->avariaCobrancas?->count() ?? 0;
        $calcao = $reserva->calcao ?? null;
        $codigoReservaExibicao = $reserva->codigoReserva?->codigo ?? ('#' . $reserva->id);
        $clienteNome = $reserva->usuario->nome ?? 'Não informado';
        $clientePartes = preg_split('/\s+/', trim((string) $clienteNome)) ?: [];
        $clienteIniciais = collect($clientePartes)
            ->filter()
            ->take(2)
            ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
            ->implode('');
        $clienteIniciais = $clienteIniciais ?: 'CL';
        $grupoFrotaNome = $reserva->grupoFrota?->nome ?? null;
        $cupomCodigo = $reserva->cupomDesconto?->codigo ?? null;
        $createdAt = $reserva->created_at ? Carbon::parse($reserva->created_at)->format('d/m/Y H:i') : '-';
        $inicioFormatado = $reserva->data_inicio ? Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') : '-';
        $fimFormatado = $reserva->data_fim ? Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') : '-';
        $inicioData = $reserva->data_inicio ? Carbon::parse($reserva->data_inicio)->format('d/m/Y') : '-';
        $inicioHora = $reserva->data_inicio ? Carbon::parse($reserva->data_inicio)->format('H:i') : '';
        $fimData = $reserva->data_fim ? Carbon::parse($reserva->data_fim)->format('d/m/Y') : '-';
        $fimHora = $reserva->data_fim ? Carbon::parse($reserva->data_fim)->format('H:i') : '';

        return compact(
            'diasReserva',
            'tarifaBase',
            'valorTarifaBase',
            'valorOpcionais',
            'percentualDesconto',
            'taxaColeta',
            'taxaEntrega',
            'subtotal',
            'valorDesconto',
            'valorTotal',
            'statusReservaNome',
            'statusReservaSlug',
            'statusReservaClass',
            'statusIcon',
            'grupoFrotaNome',
            'cupomCodigo',
            'createdAt',
            'inicioFormatado',
            'fimFormatado',
            'inicioData',
            'inicioHora',
            'fimData',
            'fimHora',
            'clienteNome',
            'clientePartes',
            'clienteIniciais',
            'reservaConfirmada',
            'nfseNaoEmitida',
            'nfseAtual',
            'nfseStatusLabel',
            'nfseMotivo',
            'nfseErroDetalhado',
            'sinistros',
            'totalSinistros',
            'danosReserva',
            'danosPendentes',
            'totalDano',
            'totalMultas',
            'totalCobrancasAvaria',
            'calcao',
            'codigoReservaExibicao',
            'transacoesReserva',
            'statusPagamentoAprovado',
            'statusPagamentoPendente',
            'statusPagamentoEstornado',
            'transacoesPagamento',
            'transacoesEstorno',
            'transacoesPendentes',
            'totalPagamentos',
            'totalEstornos',
            'totalPagoAprovado',
            'pagamentoEfetuado',
            'saldoPendenteAssinatura',
            'pagamentoEmDiaAssinatura'
        );
    }
}
