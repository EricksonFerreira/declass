@extends('adminlte::page')

@section('title', $titulo ?? 'Pagamento da minha reserva')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $moeda = strtoupper((string) ($moedaPadrao ?? 'BRL'));
    $simbolo = $moeda === 'USD' ? '$' : ($moeda === 'EUR' ? 'EUR' : 'R$');

    $codigoExibicao = $reserva->codigoReserva?->codigo ?? ('#' . $reserva->id);
    $clientePagamento = $reserva->usuario;

    $dias = (int) ($reserva->quantidade_dias ?? 0);
    $tarifaBase = (float) ($reserva->tarifa_base ?? 0);
    $valorTarifaBase = $tarifaBase * $dias;
    $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);
    if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
        $valorOpcionais = (float) $reserva->opcionais->sum(fn ($o) => (float) ($o->pivot->preco_total ?? 0));
    }
    $percentualDesconto = (float) ($reserva->percentual_desconto ?? 0);
    $cupomCodigo = $reserva->cupomDesconto?->codigo ?? null;
    $subtotal = $valorTarifaBase + $valorOpcionais + (float)($taxaColeta ?? 0) + (float)($taxaEntrega ?? 0);
    $valorDesconto = $subtotal * ($percentualDesconto / 100);
    $valorTotalReserva = max(0, $subtotal - $valorDesconto);
    $statusPagamentoAprovado = ['succeeded', 'approved', 'paid', 'completed', 'complete', 'confirmed', 'concluido'];
    $totalPagoAprovado = (float) ($totalPagoAprovado ?? ($reserva->transacoes ?? collect())
        ->filter(fn ($item) => (float) ($item->valor ?? 0)
            && in_array(strtolower((string) ($item->status ?? '')), $statusPagamentoAprovado, true))
        ->sum('valor'));
    $saldoPendente = (float) ($saldoPendente ?? max(0, $valorTotalReserva - $totalPagoAprovado));
    $valorCobranca = (float) ($valorCobranca ?? $saldoPendente);

    $cardPaymentAction = route('reserva.processarPagamento', $reserva->id);
    $backRoute = route('reserva.minhas.show', ['id' => $codigoExibicao]);
    $adicionarMetodoPagamentoUrl = route('usuario.adicionarMetodoPagamento', $reserva->user_id);
    $dataInicioPagamento = $reserva->data_inicio;
    $dataFimPagamento = $reserva->data_fim;
    $estacaoInicioNome = $reserva->estacaoRetirada?->nome ?? 'Local não informado';
    $estacaoFimNome = $reserva->estacaoDevolucao?->nome ?? 'Local não informado';
    $resumoPeriodoTexto = $reserva->grupoFrota?->nome ?? 'Grupo não informado';
    if ($reserva->modelos?->nome) {
        $resumoPeriodoTexto .= ' · ' . $reserva->modelos->nome;
    }
    $gateway = $gatewayPadrao ?? 'stripe';
@endphp

<div class="ep-page">
    <div class="ep-hero mb-4">
        <div class="ep-hero__left">
            <div class="ep-hero__icon"><i class="fas fa-credit-card"></i></div>
            <div>
                <div class="ep-hero__eyebrow">MINHA RESERVA {{ $codigoExibicao }}</div>
                <h1 class="ep-hero__title">Faturamento e Pagamento</h1>
                <p class="ep-hero__sub">Confirme os valores e registre o pagamento da reserva.</p>
            </div>
        </div>
        <div class="ep-hero__right">
            <div class="ep-hero__kpis">
                <div class="ep-kpi">
                    <div class="ep-kpi__label">VALOR A COBRAR</div>
                    <div class="ep-kpi__val">{{ $simbolo }} {{ number_format($valorCobranca, 2, ',', '.') }}</div>
                </div>
                <div class="ep-kpi ep-kpi--mid">
                    <div class="ep-kpi__label">DURAÇÃO</div>
                    <div class="ep-kpi__val">{{ $dias }} {{ $dias === 1 ? 'dia' : 'dias' }}</div>
                </div>
                @if($percentualDesconto > 0)
                <div class="ep-kpi ep-kpi--gold">
                    <div class="ep-kpi__label">DESCONTO</div>
                    <div class="ep-kpi__val ep-kpi__val--gold">{{ number_format($percentualDesconto, 0) }}%</div>
                </div>
                @endif
            </div>
            <a href="{{ $backRoute }}" class="ep-hero__back">
                <i class="fas fa-arrow-left mr-1"></i> Voltar à reserva
            </a>
        </div>
    </div>

    @foreach(['success','warning','error'] as $type)
        @if(session($type))
            <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show mb-3">
                {{ session($type) }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
    @endforeach
    @if($errors->any())
        <div class="alert alert-danger mb-3">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-lg-7 mb-4">
            <x-pagamento.cartao-stripe
                id="minhasReservaComumPagamento"
                :action="$cardPaymentAction"
                :adicionar-url="$adicionarMetodoPagamentoUrl"
                :stripe-public-key="$stripePublicKey"
                :metodos-pagamento="$metodosPagamento"
                :valor="$valorCobranca"
                :simbolo="$simbolo"
                :moeda="$moeda"
                titulo="Processar Pagamento"
                subtitulo="Cartão de crédito"
                descricao="Selecione o cartão salvo e informe a quantia a pagar (integral ou parcial)."
                :valor-readonly="false"
                :show-installments="true"
                :hidden-inputs="['metodo_pagamento' => 'cartao_credito']"
                submit-label="Confirmar e Processar Pagamento"
            />

            @if(isset($pagamentosAnteriores) && !$pagamentosAnteriores->isEmpty())
                <div class="ep-card mt-4">
                    <div class="ep-card__header ep-card__header--green">
                        <div class="ep-card__icon ep-card__icon--green"><i class="fas fa-history"></i></div>
                        <div>
                            <div class="ep-card__title">Pagamentos Anteriores</div>
                            <div class="ep-card__sub">Histórico de transações quitadas</div>
                        </div>
                    </div>
                    <div class="ep-card__body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" style="font-size: .86rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="pl-3">Data</th>
                                        <th>Valor Pago</th>
                                        <th>Método</th>
                                        <th>Referência</th>
                                        <th class="pr-3 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pagamentosAnteriores as $pagAnterior)
                                        <tr>
                                            <td class="pl-3 font-weight-bold">
                                                {{ $pagAnterior->pago_em ? \Carbon\Carbon::parse($pagAnterior->pago_em)->format('d/m/Y H:i') : ($pagAnterior->created_at ? \Carbon\Carbon::parse($pagAnterior->created_at)->format('d/m/Y H:i') : '-') }}
                                            </td>
                                            <td class="text-success font-weight-bold">
                                                {{ $simbolo }} {{ number_format((float)($pagAnterior->valor ?? 0), 2, ',', '.') }}
                                            </td>
                                            <td>{{ $pagAnterior->metodo_pagamento_label ?? $pagAnterior->metodo_pagamento ?? '-' }}</td>
                                            <td><small class="text-muted">{{ $pagAnterior->gateway_transaction_id ?? '-' }}</small></td>
                                            <td class="pr-3 text-right">
                                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Pago</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-5 mb-4">
            <div class="ep-card mb-4">
                <div class="ep-card__header ep-card__header--orange">
                    <div class="ep-card__icon ep-card__icon--orange"><i class="fas fa-calculator"></i></div>
                    <div>
                        <div class="ep-card__title">Resumo Financeiro</div>
                        <div class="ep-card__sub">Detalhamento dos valores da reserva</div>
                    </div>
                </div>
                <div class="ep-card__body">
                    <div class="ep-summary-row">
                        <span>Tarifa base</span>
                        <strong>{{ $simbolo }} {{ number_format($valorTarifaBase, 2, ',', '.') }}</strong>
                    </div>
                    @if($valorOpcionais > 0)
                        <div class="ep-summary-row">
                            <span>Opcionais</span>
                            <strong>+ {{ $simbolo }} {{ number_format($valorOpcionais, 2, ',', '.') }}</strong>
                        </div>
                    @endif
                    @if(($taxaColeta ?? 0) > 0)
                        <div class="ep-summary-row">
                            <span>Taxa de coleta</span>
                            <strong>+ {{ $simbolo }} {{ number_format((float)$taxaColeta, 2, ',', '.') }}</strong>
                        </div>
                    @endif
                    @if(($taxaEntrega ?? 0) > 0)
                        <div class="ep-summary-row">
                            <span>Taxa de entrega</span>
                            <strong>+ {{ $simbolo }} {{ number_format((float)$taxaEntrega, 2, ',', '.') }}</strong>
                        </div>
                    @endif
                    @if($percentualDesconto > 0)
                        <div class="ep-summary-row text-success">
                            <span>Desconto ({{ number_format($percentualDesconto, 0) }}%)</span>
                            <strong class="text-success">- {{ $simbolo }} {{ number_format($valorDesconto, 2, ',', '.') }}</strong>
                        </div>
                    @endif
                    @if($totalPagoAprovado > 0)
                        <div class="ep-summary-row text-success font-weight-bold">
                            <span>Total Já Pago</span>
                            <strong class="text-success">- {{ $simbolo }} {{ number_format($totalPagoAprovado, 2, ',', '.') }}</strong>
                        </div>
                    @endif
                    @if(!empty($cupomCodigo))
                        <div class="ep-note mt-2"><i class="fas fa-tag text-primary"></i><span>Cupom: {{ $cupomCodigo }}</span></div>
                    @endif
                    <div class="ep-total-final">
                        <span class="ep-total-final__label">Saldo Pendente</span>
                        <span class="ep-total-final__val">{{ $simbolo }} {{ number_format($saldoPendente, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="ep-card">
                <div class="ep-card__header ep-card__header--blue">
                    <div class="ep-card__icon"><i class="fas fa-file-contract"></i></div>
                    <div>
                        <div class="ep-card__title">Dados da Reserva</div>
                        <div class="ep-card__sub">Informações operacionais da locação</div>
                    </div>
                </div>
                <div class="ep-card__body">
                    <div class="ep-client mb-3 pb-3" style="border-bottom:1px solid #f1f5f9;">
                        <div class="ep-client__avatar">{{ strtoupper(substr($clientePagamento?->nome ?? $clientePagamento?->name ?? 'C', 0, 1)) }}</div>
                        <div>
                            <div class="ep-client__name">{{ $clientePagamento?->nome ?? $clientePagamento?->name ?? 'Cliente' }}</div>
                            <div class="ep-client__info"><i class="fas fa-envelope mr-1 text-muted"></i>{{ $clientePagamento?->email ?? '-' }}</div>
                            @if(!empty($clientePagamento?->telefone))
                                <div class="ep-client__info"><i class="fas fa-phone mr-1 text-muted"></i>{{ $clientePagamento->telefone }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="ep-summary-row"><span>Código</span><strong>{{ $codigoExibicao }}</strong></div>
                    <div class="ep-summary-row"><span>Grupo</span><strong>{{ $reserva->grupoFrota?->nome ?? '-' }}</strong></div>
                    <div class="ep-summary-row"><span>Modelo</span><strong>{{ $reserva->modelos?->nome ?? '-' }}</strong></div>
                    <div class="ep-summary-row"><span>Retirada</span><strong>{{ $estacaoInicioNome }}</strong></div>
                    <div class="ep-summary-row"><span>Devolução</span><strong>{{ $estacaoFimNome }}</strong></div>
                    <div class="ep-summary-row"><span>Vigência</span><strong>{{ $dataInicioPagamento ? \Carbon\Carbon::parse($dataInicioPagamento)->format('d/m/Y H:i') : '-' }} até {{ $dataFimPagamento ? \Carbon\Carbon::parse($dataFimPagamento)->format('d/m/Y H:i') : '-' }}</strong></div>
                    <div class="ep-summary-row"><span>Duração</span><strong>{{ $dias }} {{ $dias === 1 ? 'dia' : 'dias' }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.ep-page { --ep-blue:#4b80ff; --ep-navy:#152c49; --ep-muted:#6c7a8d; --ep-border:#e0e7ef; }
.ep-hero { background:linear-gradient(135deg,#0f172a,#173d63 55%,#2563eb); color:#fff; border-radius:20px; padding:22px; display:flex; justify-content:space-between; gap:18px; flex-wrap:wrap; box-shadow:0 16px 38px rgba(15,23,42,.16); }
.ep-hero__left { display:flex; gap:14px; align-items:center; }
.ep-hero__icon { width:58px; height:58px; border-radius:18px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.14); font-size:1.5rem; }
.ep-hero__eyebrow { font-size:.72rem; letter-spacing:.16em; text-transform:uppercase; font-weight:900; color:rgba(255,255,255,.76); }
.ep-hero__title { margin:2px 0 4px; font-weight:900; font-size:1.55rem; }
.ep-hero__sub { margin:0; color:rgba(255,255,255,.78); }
.ep-hero__right { display:flex; align-items:center; gap:14px; flex-wrap:wrap; justify-content:flex-end; }
.ep-hero__kpis { display:flex; gap:8px; flex-wrap:wrap; }
.ep-kpi { background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18); border-radius:14px; padding:10px 12px; min-width:120px; }
.ep-kpi__label { font-size:.62rem; letter-spacing:.12em; text-transform:uppercase; font-weight:900; color:rgba(255,255,255,.70); }
.ep-kpi__val { font-size:1.05rem; font-weight:900; margin-top:3px; }
.ep-kpi--gold { background:rgba(250,204,21,.16); border-color:rgba(250,204,21,.32); }
.ep-kpi__val--gold { color:#fef08a; }
.ep-hero__back { color:#fff; border:1px solid rgba(255,255,255,.22); background:rgba(255,255,255,.10); border-radius:999px; padding:9px 13px; font-weight:800; text-decoration:none; }
.ep-card { background:#fff; border:1px solid var(--ep-border); border-radius:18px; overflow:hidden; box-shadow:0 10px 24px rgba(15,23,42,.06); }
.ep-card__header { padding:14px 16px; display:flex; align-items:center; gap:12px; border-bottom:1px solid #edf2f8; }
.ep-card__header--blue { background:#f5f9ff; }
.ep-card__header--green { background:#f0fdf4; }
.ep-card__header--orange { background:#fff7ed; }
.ep-card__icon { width:38px; height:38px; border-radius:12px; background:#dbeafe; color:#2563eb; display:flex; align-items:center; justify-content:center; }
.ep-card__icon--green { background:#dcfce7; color:#16a34a; }
.ep-card__icon--orange { background:#fed7aa; color:#ea580c; }
.ep-card__title { font-weight:900; color:#0f172a; }
.ep-card__sub { color:#64748b; font-size:.8rem; }
.ep-card__body { padding:18px; }
.ep-client { display:flex; gap:12px; align-items:center; }
.ep-client__avatar { width:48px; height:48px; border-radius:50%; background:#4b80ff; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:900; }
.ep-client__name { font-weight:900; color:#0f172a; }
.ep-client__info { color:#64748b; font-size:.86rem; }
.ep-tip { background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; border-radius:12px; padding:12px; }
.ep-form-label { font-size:.8rem; font-weight:900; color:#0f172a; }
.ep-summary-row { display:flex; justify-content:space-between; gap:14px; padding:10px 0; border-bottom:1px solid #edf2f8; }
.ep-summary-row span { color:#64748b; }
.ep-summary-row strong { color:#0f172a; text-align:right; }
.ep-note { background:#f8fafc; border:1px solid #dbe4ee; border-radius:12px; padding:10px; display:flex; gap:8px; color:#334155; }
.ep-total-final { margin-top:14px; border:1px solid #2563eb; background:#f8fbff; border-radius:14px; padding:14px; display:flex; justify-content:space-between; align-items:center; }
.ep-total-final__label { color:#0f172a; font-weight:900; }
.ep-total-final__val { color:#1d4ed8; font-size:1.25rem; font-weight:900; }
@media (max-width: 768px) { .ep-hero__left, .ep-hero__right { width:100%; justify-content:flex-start; } .ep-hero__kpis { width:100%; } .ep-kpi { flex:1 1 100%; } }
</style>
@stop

@section('js')
@stack('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('payment-form');
    var btn = document.getElementById('btn-submit-pagamento');
    if (form && btn) {
        form.addEventListener('submit', function () {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processando...';
        });
    }
});
</script>
@stop
