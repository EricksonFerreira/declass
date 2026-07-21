@extends('adminlte::page')

@section('title', $titulo ?? 'Pagamento Motorista APP')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $moeda = strtoupper((string) ($moedaPadrao ?? 'BRL'));
    $simbolo = $moeda === 'USD' ? '$' : ($moeda === 'EUR' ? 'EUR' : 'R$');
    $codigoReserva = $reservaMotoristaApp->codigoReserva?->codigo ?? ('MAP-' . str_pad((string) $reservaMotoristaApp->id, 6, '0', STR_PAD_LEFT));
    $cliente = $pagamento->user ?? $reservaMotoristaApp->user;
    $plano = $reservaMotoristaApp->planoMotoristaApp;
    $periodos = (int) ($pagamento->periodos ?? 1);
    $periodos = max(1, $periodos);
    $valorUnitarioContrato = (float) ($reservaMotoristaApp->valor_periodo_contrato ?? 0);
    $valorUnitarioReserva = (float) ($reservaMotoristaApp->valor_plano ?? 0);
    $valorUnitarioPlano = (float) ($plano?->valor_periodo ?? 0);
    $valorUnitarioPagamento = (float) ($pagamento->valor_unitario ?? 0);
    $valorUnitario = $valorUnitarioContrato > 0
        ? $valorUnitarioContrato
        : ($valorUnitarioReserva > 0
            ? $valorUnitarioReserva
            : ($valorUnitarioPlano > 0 ? $valorUnitarioPlano : $valorUnitarioPagamento));
    $valorOpcionaisReserva = (float) ($reservaMotoristaApp->valor_opcionais ?? 0);
    $valorBaseCalculado = round($valorUnitario * $periodos, 2);
    $valorTotalCicloOriginal = round($valorBaseCalculado + $valorOpcionaisReserva, 2);

    $totalPagoAcumulado = (float) $reservaMotoristaApp->pagamentos()
        ->where('status', 'pago')
        ->sum('valor_total');

    $valorCobranca = max(0, round($valorTotalCicloOriginal - $totalPagoAcumulado, 2));
    $inicio = $pagamento->data_inicio_prevista ? \Carbon\Carbon::parse($pagamento->data_inicio_prevista)->format('d/m/Y H:i') : '-';
    $fim = $pagamento->data_fim_prevista ? \Carbon\Carbon::parse($pagamento->data_fim_prevista)->format('d/m/Y H:i') : '-';
    $dias = ($pagamento->data_inicio_prevista && $pagamento->data_fim_prevista)
        ? max(1, \Carbon\Carbon::parse($pagamento->data_inicio_prevista)->diffInDays(\Carbon\Carbon::parse($pagamento->data_fim_prevista)))
        : (int) ($reservaMotoristaApp->quantidade_dias ?? 0);
    $statusAtual = strtolower((string) ($pagamento->status ?? 'pendente'));
@endphp

<div class="ep-page">
    <div class="ep-hero mb-4">
        <div class="ep-hero__left">
            <div class="ep-hero__icon"><i class="fas fa-credit-card"></i></div>
            <div>
                <div class="ep-hero__eyebrow">RESERVA {{ $codigoReserva }}</div>
                <h1 class="ep-hero__title">Faturamento e Pagamento</h1>
                <p class="ep-hero__sub">Confirme os valores e registre o pagamento do ciclo motorista APP.</p>
            </div>
        </div>
        <div class="ep-hero__right">
            <div class="ep-hero__kpis">
                <div class="ep-kpi">
                    <div class="ep-kpi__label">TOTAL DO CICLO</div>
                    <div class="ep-kpi__val">{{ $simbolo }} {{ number_format($valorTotalCicloOriginal, 2, ',', '.') }}</div>
                </div>
                @if($totalPagoAcumulado > 0)
                    <div class="ep-kpi ep-kpi--green">
                        <div class="ep-kpi__label">JÁ PAGO</div>
                        <div class="ep-kpi__val ep-kpi__val--green" style="color:#86efac;">{{ $simbolo }} {{ number_format($totalPagoAcumulado, 2, ',', '.') }}</div>
                    </div>
                @endif
                <div class="ep-kpi ep-kpi--gold">
                    <div class="ep-kpi__label">SALDO PENDENTE</div>
                    <div class="ep-kpi__val ep-kpi__val--gold">{{ $simbolo }} {{ number_format($valorCobranca, 2, ',', '.') }}</div>
                </div>
            </div>
            <a href="{{ route('reserva.show', $codigoReserva) }}" class="ep-hero__back">
                <i class="fas fa-arrow-left mr-1"></i> Voltar à Reserva
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
        {{-- Coluna Esquerda: Stripe + Pagamento Manual + Histórico --}}
        <div class="col-lg-7 mb-4">
            <x-pagamento.cartao-stripe
                id="motoristaAppPagamento"
                :action="route('reserva.pagamento.confirmar', $pagamento->id)"
                :adicionar-url="route('usuario.adicionarMetodoPagamento', $reservaMotoristaApp->user_id)"
                :stripe-public-key="$stripePublicKey"
                :metodos-pagamento="$metodosPagamento"
                :valor="$valorCobranca"
                :simbolo="$simbolo"
                :moeda="$moeda"
                titulo="Processar Pagamento via Cartão"
                subtitulo="Cartão de crédito"
                descricao="Selecione o cartão salvo ou adicione um novo para processar o pagamento."
                :valor-readonly="false"
                :show-installments="true"
                :hidden-inputs="['metodo_pagamento' => 'cartao_credito']"
                submit-label="Confirmar e Processar Pagamento"
            />

            <div class="ep-card mb-4">
                <div class="ep-card__header ep-card__header--orange">
                    <div class="ep-card__icon ep-card__icon--orange"><i class="fas fa-cash-register"></i></div>
                    <div>
                        <div class="ep-card__title">Registrar pagamento manual</div>
                        <div class="ep-card__sub">Uso administrativo para pagamento presencial ou externo</div>
                    </div>
                </div>
                <div class="ep-card__body">
                    <div class="ep-tip mb-3">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        Use esta opção quando o cliente pagar na empresa ou por um meio externo.
                    </div>
                    <form id="manual-payment-form" action="{{ route('reserva.pagamento.confirmar', $pagamento->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="ep-form-label"><i class="fas fa-wallet mr-1"></i> Método de pagamento</label>
                            <select name="metodo_pagamento" class="form-control form-control-sm" required>
                                <option value="dinheiro">Dinheiro</option>
                                <option value="pix">PIX</option>
                                <option value="cartao_credito">Cartão de crédito presencial</option>
                                <option value="cartao_debito">Cartão de débito presencial</option>
                                <option value="boleto">Boleto</option>
                                <option value="transferencia">Transferência</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="ep-form-label"><i class="fas fa-dollar-sign mr-1"></i> Valor recebido</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">{{ $simbolo }}</span></div>
                                <input type="number" name="valor_pagamento" class="form-control form-control-sm" step="0.01" min="0.01" max="{{ $valorCobranca }}" value="{{ $valorCobranca }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="ep-form-label"><i class="fas fa-hashtag mr-1"></i> Referência</label>
                            <input type="text" name="referencia_pagamento" class="form-control form-control-sm" placeholder="Comprovante, NSU, observação ou referência interna">
                        </div>
                        <button type="submit" class="btn btn-outline-success btn-block">
                            <i class="fas fa-check mr-2"></i> Registrar pagamento manual
                        </button>
                    </form>
                </div>
            </div>

            @if(isset($pagamentosAnteriores) && !$pagamentosAnteriores->isEmpty())
                <div class="ep-card mt-4">
                    <div class="ep-card__header ep-card__header--green">
                        <div class="ep-card__icon ep-card__icon--green"><i class="fas fa-history"></i></div>
                        <div>
                            <div class="ep-card__title">Pagamentos Anteriores da Reserva</div>
                            <div class="ep-card__sub">Histórico de parcelas e adiantamentos quitados</div>
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
                                                {{ $pagAnterior->pago_em ? \Carbon\Carbon::parse($pagAnterior->pago_em)->format('d/m/Y H:i') : ($pagAnterior->updated_at ? \Carbon\Carbon::parse($pagAnterior->updated_at)->format('d/m/Y H:i') : '-') }}
                                            </td>
                                            <td class="text-success font-weight-bold">
                                                {{ $simbolo }} {{ number_format((float)($pagAnterior->valor_total ?? 0), 2, ',', '.') }}
                                            </td>
                                            <td>{{ $pagAnterior->metodo_label ?? ucfirst(str_replace('_', ' ', (string)($pagAnterior->metodo_pagamento ?? '-'))) }}</td>
                                            <td><small class="text-muted">{{ $pagAnterior->referencia_pagamento ?? '-' }}</small></td>
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

        {{-- Coluna Direita: Resumo Financeiro e Detalhes da Reserva --}}
        <div class="col-lg-5 mb-4">
            <div class="ep-card mb-4">
                <div class="ep-card__header ep-card__header--orange">
                    <div class="ep-card__icon ep-card__icon--orange"><i class="fas fa-calculator"></i></div>
                    <div>
                        <div class="ep-card__title">Resumo Financeiro do Ciclo</div>
                        <div class="ep-card__sub">Detalhamento dos valores e saldo pendente</div>
                    </div>
                </div>
                <div class="ep-card__body">
                    <div class="ep-summary-row"><span>Valor do plano (ciclo)</span><strong>{{ $simbolo }} {{ number_format($valorUnitario, 2, ',', '.') }}</strong></div>
                    @if($valorOpcionaisReserva > 0)
                        <div class="ep-summary-row"><span>Opcionais inclusos</span><strong>+ {{ $simbolo }} {{ number_format($valorOpcionaisReserva, 2, ',', '.') }}</strong></div>
                    @endif
                    <div class="ep-summary-row font-weight-bold" style="border-top:1px dashed #cbd5e1; margin-top:4px; padding-top:8px;">
                        <span>Total Original do Ciclo</span>
                        <strong style="color:#0f172a;">{{ $simbolo }} {{ number_format($valorTotalCicloOriginal, 2, ',', '.') }}</strong>
                    </div>
                    @if($totalPagoAcumulado > 0)
                        <div class="ep-summary-row text-success font-weight-bold">
                            <span>Total Já Pago</span>
                            <strong class="text-success">- {{ $simbolo }} {{ number_format($totalPagoAcumulado, 2, ',', '.') }}</strong>
                        </div>
                    @endif
                    <div class="ep-summary-row"><span>Status Atual</span><strong><span class="badge badge-warning text-uppercase" style="background:#fef3c7; color:#92400e;">{{ ucfirst($statusAtual) }}</span></strong></div>
                    @if(!empty($pagamento->observacoes))
                        <div class="ep-note mt-3">
                            <i class="fas fa-sticky-note text-primary"></i>
                            <span>{{ $pagamento->observacoes }}</span>
                        </div>
                    @endif
                    <div class="ep-total-final">
                        <span class="ep-total-final__label">Saldo Pendente a Cobrar</span>
                        <span class="ep-total-final__val">{{ $simbolo }} {{ number_format($valorCobranca, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="ep-card">
                <div class="ep-card__header ep-card__header--blue">
                    <div class="ep-card__icon"><i class="fas fa-file-contract"></i></div>
                    <div>
                        <div class="ep-card__title">Dados da Reserva & Titular</div>
                        <div class="ep-card__sub">Informações operacionais da locação</div>
                    </div>
                </div>
                <div class="ep-card__body">
                    <div class="ep-client mb-3 pb-3" style="border-bottom:1px solid #f1f5f9;">
                        <div class="ep-client__avatar">{{ strtoupper(substr($cliente?->nome ?? $cliente?->name ?? 'C', 0, 1)) }}</div>
                        <div>
                            <div class="ep-client__name">{{ $cliente?->nome ?? $cliente?->name ?? 'Cliente' }}</div>
                            <div class="ep-client__info"><i class="fas fa-envelope mr-1 text-muted"></i>{{ $cliente?->email ?? '-' }}</div>
                            @if(!empty($cliente?->telefone))
                                <div class="ep-client__info"><i class="fas fa-phone mr-1 text-muted"></i>{{ $cliente->telefone }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="ep-summary-row"><span>Código da Reserva</span><strong>{{ $codigoReserva }}</strong></div>
                    <div class="ep-summary-row"><span>Plano Contratado</span><strong>{{ $plano?->nome ?? 'Motorista APP' }}</strong></div>
                    <div class="ep-summary-row"><span>Veículo Alocado</span><strong>{{ $reservaMotoristaApp->veiculo?->nome ?? 'Aguardando alocação' }}</strong></div>
                    <div class="ep-summary-row"><span>Local de Retirada</span><strong>{{ $reservaMotoristaApp->estacaoRetirada?->nome ?? '-' }}</strong></div>
                    <div class="ep-summary-row"><span>Vigência</span><strong>{{ $inicio }} até {{ $fim }}</strong></div>
                    <div class="ep-summary-row"><span>Duração</span><strong>{{ $dias }} {{ $dias === 1 ? 'dia' : 'dias' }} ({{ $periodos }} {{ $periodos === 1 ? 'ciclo' : 'ciclos' }})</strong></div>
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
.ep-kpi__label { font-size:.65rem; font-weight:900; letter-spacing:.08em; color:rgba(255,255,255,.68); }
.ep-kpi__val { font-size:1.05rem; font-weight:900; }
.ep-kpi__val--gold { color:#fde68a; }
.ep-hero__back { color:#fff; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.10); border-radius:999px; padding:9px 13px; font-weight:800; text-decoration:none; }
.ep-card { background:#fff; border:1px solid var(--ep-border); border-radius:16px; overflow:hidden; box-shadow:0 10px 26px rgba(15,23,42,.06); }
.ep-card__header { padding:14px 16px; display:flex; align-items:center; gap:10px; border-bottom:1px solid #eef2f7; background:#f8fbff; }
.ep-card__header--gray { background:#f8fafc; }
.ep-card__header--green { background:#f0fdf4; }
.ep-card__header--orange { background:#fff7ed; }
.ep-card__icon { width:42px; height:42px; border-radius:13px; display:flex; align-items:center; justify-content:center; background:#dbeafe; color:#2563eb; }
.ep-card__icon--gray { background:#e2e8f0; color:#475569; }
.ep-card__icon--green { background:#dcfce7; color:#15803d; }
.ep-card__icon--orange { background:#fed7aa; color:#c2410c; }
.ep-card__title { font-weight:900; color:#152c49; }
.ep-card__sub { font-size:.8rem; color:var(--ep-muted); }
.ep-card__body { padding:16px; }
.ep-client { display:flex; align-items:center; gap:.85rem; }
.ep-client__avatar { width:44px; height:44px; border-radius:50%; background:linear-gradient(135deg,#4b80ff,#6f9fff); display:flex; align-items:center; justify-content:center; font-weight:900; color:#fff; }
.ep-client__name { font-weight:800; color:#21364f; }
.ep-client__info { font-size:.82rem; color:#6c757d; }
.ep-period__label { font-size:.68rem; font-weight:900; letter-spacing:.06em; color:#9aacbf; text-transform:uppercase; }
.ep-period__station { font-weight:800; color:#21364f; font-size:.9rem; }
.ep-period__date { font-size:.82rem; color:#6c757d; }
.ep-period__dias { background:#f4f8ff; border-radius:8px; padding:.55rem .85rem; font-size:.84rem; color:#2c4a6e; margin-top:.85rem; }
.ep-tip { display:flex; align-items:flex-start; gap:.5rem; font-size:.84rem; background:#f4f8ff; border:1px solid #d0e3ff; border-radius:10px; padding:.65rem .9rem; color:#2c4a6e; }
.ep-form-label { font-weight:800; font-size:.84rem; color:#2c4a6e; margin-bottom:.35rem; display:block; }
.ep-total-box { background:linear-gradient(120deg,#edfff4,#e8f9f0); border:1px solid #c3ecd4; border-radius:12px; padding:1rem 1.2rem; display:flex; align-items:center; justify-content:space-between; }
.ep-total-box__label { font-size:.82rem; font-weight:800; color:#4a7a5a; }
.ep-total-box__value { font-size:1.5rem; font-weight:900; color:#1a5c35; }
.ep-btn-pay { border-radius:10px; font-weight:800; font-size:.95rem; padding:.7rem; }
.ep-summary-row { display:flex; justify-content:space-between; align-items:center; padding:.42rem 0; font-size:.86rem; color:#495057; border-bottom:1px solid #f0f3f8; gap:12px; }
.ep-total-final { display:flex; justify-content:flex-end; align-items:center; gap:.6rem; border:2px solid #4b80ff; border-radius:10px; padding:.75rem .9rem; margin-top:.8rem; background:#f4f8ff; }
.ep-total-final__label { color:#21364f; font-weight:800; margin-left:auto; }
.ep-total-final__val { font-weight:900; color:#0f2740; font-size:1.12rem; }
.ep-note { border:1px solid #e0e7ef; border-radius:8px; background:#f8faff; padding:.6rem .85rem; display:flex; align-items:flex-start; gap:.4rem; }
@media (max-width: 768px) { .ep-hero__right { justify-content:flex-start; } .ep-kpi { min-width:100%; } }
</style>
@stop

@section('js')
@stack('js')
<script>
document.getElementById('manual-payment-form')?.addEventListener('submit', function () {
    var btn = this.querySelector('button[type="submit"]');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Processando...'; }
});
</script>
@stop
