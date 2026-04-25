@extends('adminlte::page')

@section('title', 'Meu Plano')

@section('content_header')
    <div>
        <h1 class="m-0 text-dark" style="font-size:1.3rem;font-weight:700;">Plano &amp; Pagamentos</h1>
        <small class="text-muted">Gerencie sua assinatura e visualize o histórico financeiro</small>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Abas de navegação --}}
    <ul class="nav nav-tabs mb-0" id="planTabs" role="tablist"
        style="border-bottom:2px solid #0a1628;">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" id="tab-plano" data-toggle="tab" href="#pane-plano" role="tab"
               style="color:#0a1628;border-color:#0a1628 #0a1628 #fff;">
                <i class="fas fa-id-card mr-1"></i> Meu Plano
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" id="tab-historico" data-toggle="tab" href="#pane-historico" role="tab"
               style="color:#6c757d;">
                <i class="fas fa-history mr-1"></i> Histórico
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" id="tab-planos" data-toggle="tab" href="#pane-planos" role="tab"
               style="color:#6c757d;">
                <i class="fas fa-layer-group mr-1"></i> Trocar Plano
            </a>
        </li>
    </ul>

    <div class="tab-content" style="background:#fff;border:1px solid #dee2e6;border-top:none;border-radius:0 0 8px 8px;padding:1.25rem;">

        {{-- ══════════════════════════════════════════════════════════════════
             ABA 1 — MEU PLANO ATUAL
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="pane-plano" role="tabpanel">
            @if (!$assinatura)
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5 class="font-weight-bold">Nenhum plano ativo</h5>
                    <p class="text-muted mb-3">Escolha um plano para liberar todos os recursos do sistema.</p>
                    <button type="button" class="btn btn-primary" onclick="irParaAba('tab-planos')">
                        <i class="fas fa-layer-group mr-1"></i> Ver planos disponíveis
                    </button>
                </div>
            @else
                @php
                    $badge = $assinatura->status_badge;
                    $dias  = $assinatura->diasParaVencer();
                    $planoPago = $assinatura->estaAtivo();
                    $proximoPagamento = $empresa->pagamentosPlano()
                        ->where('status', 'pendente')
                        ->orderBy('data_vencimento')
                        ->first();
                    $ultimoPagamentoPago = $empresa->pagamentosPlano()
                        ->where('status', 'pago')
                        ->orderByDesc('pago_em')
                        ->first();
                @endphp

                {{-- Alerta vencimento próximo --}}
                @if ($dias >= 0 && $dias <= 7)
                    <div class="alert alert-warning py-2 mb-3" style="font-size:.875rem;">
                        <i class="fas fa-clock mr-1"></i>
                        Seu plano vence em <strong>{{ $dias }} dia(s)</strong>. Renove para não perder o acesso.
                        <button type="button" class="btn btn-warning btn-sm ml-2"
                                data-toggle="modal" data-target="#modalRenovar">
                            Renovar agora
                        </button>
                    </div>
                @elseif ($dias < 0)
                    <div class="alert alert-danger py-2 mb-3" style="font-size:.875rem;">
                        <i class="fas fa-lock mr-1"></i>
                        Plano <strong>vencido</strong>. Renove para reativar todos os recursos.
                        <button type="button" class="btn btn-danger btn-sm ml-2"
                                data-toggle="modal" data-target="#modalRenovar">
                            Renovar agora
                        </button>
                    </div>
                @endif

                @if ($planoPago && $ultimoPagamentoPago)
                <div class="alert alert-success py-2 mb-3" style="font-size:.875rem;border-left:4px solid #22c55e;">
                    <i class="fas fa-check-circle mr-1"></i>
                    <strong>Plano ativo e em dia!</strong>
                    Último pagamento confirmado em {{ $ultimoPagamentoPago->pago_em->format('d/m/Y') }}.
                    Próximo vencimento: <strong>{{ $assinatura->data_vencimento->format('d/m/Y') }}</strong>.
                </div>
                @endif

                <div class="row">
                    {{-- Card do plano --}}
                    <div class="col-md-5 mb-3">
                        @php $borderColor = $planoPago ? '#22c55e' : '#0a1628'; $headerBg = $planoPago ? '#16a34a' : '#0a1628'; @endphp
                        <div class="card h-100" style="border:2px solid {{ $borderColor }};border-radius:12px;overflow:hidden;">
                            <div class="card-header py-2 text-white d-flex justify-content-between align-items-center"
                                 style="background:{{ $headerBg }};">
                                <span style="font-weight:700;font-size:.95rem;">
                                    <i class="fas fa-{{ $planoPago ? 'check-circle' : 'id-card' }} mr-1"></i> {{ $assinatura->plano->nome }}
                                </span>
                                <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                            </div>
                            <div class="card-body">
                                <div style="font-size:2.2rem;font-weight:900;color:#0a1628;line-height:1;">
                                    R$ {{ number_format($assinatura->valor_cobrado, 2, ',', '.') }}
                                    <small class="text-muted font-weight-normal" style="font-size:.85rem;">/mês</small>
                                </div>
                                <div class="text-muted mb-3" style="font-size:.8rem;">{{ $assinatura->periodicidade_texto }}</div>

                                <table class="table table-sm table-borderless mb-3" style="font-size:.82rem;">
                                    <tr>
                                        <td class="text-muted pl-0" style="width:40%;">Início</td>
                                        <td class="font-weight-bold">{{ $assinatura->data_inicio->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-0">Vencimento</td>
                                        <td class="font-weight-bold">
                                            {{ $assinatura->data_vencimento->format('d/m/Y') }}
                                            @if ($dias >= 0 && $dias <= 10)
                                                <span class="badge badge-warning ml-1">{{ $dias }}d</span>
                                            @elseif ($dias < 0)
                                                <span class="badge badge-danger ml-1">Vencido</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted pl-0">Cobrança</td>
                                        <td class="font-weight-bold">
                                            @php
                                                $mlabel = match($assinatura->metodo_cobranca) {
                                                    'boleto' => '<i class="fas fa-barcode mr-1"></i>Boleto',
                                                    'pix'    => '<i class="fas fa-qrcode mr-1"></i>PIX',
                                                    'cartao' => '<i class="fas fa-credit-card mr-1"></i>Cartão de Crédito',
                                                    default  => ucfirst($assinatura->metodo_cobranca),
                                                };
                                            @endphp
                                            {!! $mlabel !!}
                                            @if ($assinatura->metodo_cobranca === 'cartao' && $assinatura->renovacao_automatica)
                                                <span class="badge badge-success ml-1" style="font-size:.65rem;">
                                                    <i class="fas fa-sync-alt"></i> Auto
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>

                                @if ($assinatura->temMudancaAgendada())
                                    <div class="alert alert-warning py-2 mb-2" style="font-size:.82rem;">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        @if ($assinatura->mudanca_tipo === 'cancelamento')
                                            Cancelamento agendado para <strong>{{ $assinatura->mudanca_efetiva_em?->format('d/m/Y') }}</strong>.
                                        @else
                                            Downgrade agendado para <strong>{{ $assinatura->mudanca_efetiva_em?->format('d/m/Y') }}</strong>.
                                        @endif
                                        <form method="POST" action="{{ route('assinatura.mudanca.remover') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-link btn-sm p-0 ml-2" onclick="return confirm('Remover mudanca agendada?')">
                                                Remover
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                <div class="d-flex" style="gap:.5rem;flex-wrap:wrap;">
                                    <button type="button" class="btn btn-primary btn-sm flex-fill"
                                            data-toggle="modal" data-target="#modalRenovar">
                                        <i class="fas fa-sync-alt mr-1"></i> Renovar
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="irParaAba('tab-planos')">
                                        <i class="fas fa-exchange-alt mr-1"></i> Trocar plano
                                    </button>
                                    @if (!$assinatura->temMudancaAgendada())
                                        <form method="POST" action="{{ route('assinatura.cancelar') }}" class="flex-fill m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                                    onclick="return confirm('Agendar cancelamento para o fim do periodo vigente?')">
                                                <i class="fas fa-ban mr-1"></i> Cancelar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Itens inclusos --}}
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-header py-2">
                                <h5 class="card-title mb-0" style="font-size:.88rem;">
                                    <i class="fas fa-check-circle text-success mr-1"></i> O que está incluso
                                </h5>
                            </div>
                            <div class="card-body py-2">
                                <ul class="list-unstyled mb-0">
                                    @foreach ($assinatura->plano->itens as $item)
                                        <li class="mb-1 d-flex align-items-start" style="font-size:.82rem;">
                                            <i class="fas fa-{{ $item->incluso ? 'check text-success' : 'times text-muted' }} mr-2 mt-1 flex-shrink-0" style="font-size:.7rem;"></i>
                                            {{ $item->descricao }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Suporte --}}
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-header py-2">
                                <h5 class="card-title mb-0" style="font-size:.88rem;">
                                    <i class="fas fa-headset text-info mr-1"></i> Suporte
                                </h5>
                            </div>
                            <div class="card-body py-2" style="font-size:.82rem;">
                                <p class="mb-2 text-muted">Dúvidas sobre planos ou problemas técnicos?</p>
                                <a href="mailto:suporte@decars.com.br" class="btn btn-outline-secondary btn-sm btn-block mb-2">
                                    <i class="fas fa-envelope mr-1"></i> E-mail
                                </a>
                                <a href="https://wa.me/558192554172" target="_blank" rel="noopener"
                                   class="btn btn-outline-success btn-sm btn-block">
                                    <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── PAGAMENTO PENDENTE ────────────────────────────────────── --}}
                @if ($proximoPagamento)
                    <div class="card mt-1" style="border:2px solid #f59e0b;border-radius:12px;overflow:hidden;">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center"
                             style="background:#fffbeb;border-bottom:1px solid #fde68a;">
                            <h5 class="mb-0" style="font-size:.92rem;font-weight:700;color:#92400e;">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Pagamento Pendente
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="abrirAlterarMetodo({{ $proximoPagamento->id }}, '{{ $proximoPagamento->metodo }}')"
                                    style="font-size:.75rem;">
                                <i class="fas fa-exchange-alt mr-1"></i> Alterar forma de pagamento
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-start">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <table class="table table-sm table-borderless mb-0" style="font-size:.83rem;">
                                        <tr>
                                            <td class="text-muted pl-0">Valor</td>
                                            <td class="font-weight-bold">R$ {{ number_format($proximoPagamento->valor, 2, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted pl-0">Vencimento</td>
                                            <td class="font-weight-bold">{{ $proximoPagamento->data_vencimento->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted pl-0">Método</td>
                                            <td class="font-weight-bold">{{ $proximoPagamento->metodo_texto }}</td>
                                        </tr>
                                        @if ($proximoPagamento->referencia_interna)
                                            <tr>
                                                <td class="text-muted pl-0">Ref.</td>
                                                <td style="font-size:.75rem;">{{ $proximoPagamento->referencia_interna }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>

                                <div class="col-md-8">
                                    {{-- PIX via Stripe --}}
                                    @if ($proximoPagamento->metodo === 'pix')
                                        <div id="pixWrapper">
                                            {{-- Estado: carregando --}}
                                            <div id="pixLoading" class="text-center py-4">
                                                <div class="spinner-border text-primary mb-2" style="width:2rem;height:2rem;"></div>
                                                <p class="text-muted mb-0" style="font-size:.85rem;">Gerando QR Code PIX…</p>
                                            </div>

                                            {{-- Estado: QR Code carregado --}}
                                            <div id="pixConteudo" style="display:none;">
                                                <div class="d-flex align-items-start" style="gap:1.5rem;flex-wrap:wrap;">
                                                    {{-- QR Code --}}
                                                    <div class="text-center flex-shrink-0">
                                                        <div style="padding:8px;border:2px solid #e2e8f0;border-radius:10px;background:#fff;display:inline-block;">
                                                            <img id="pixQrImage" src="" alt="QR Code PIX"
                                                                 style="width:160px;height:160px;display:block;">
                                                        </div>
                                                        <div class="text-muted mt-1" style="font-size:.72rem;">Escaneie com seu banco</div>
                                                        <div id="pixExpira" class="mt-1" style="font-size:.72rem;color:#dc2626;font-weight:600;"></div>
                                                    </div>

                                                    <div class="flex-grow-1">
                                                        <p class="font-weight-bold mb-1" style="font-size:.92rem;color:#064e3b;">
                                                            <i class="fas fa-qrcode mr-1"></i> Pague R$ {{ number_format($proximoPagamento->valor, 2, ',', '.') }} via PIX
                                                        </p>
                                                        <p class="text-muted mb-2" style="font-size:.82rem;">
                                                            Escaneie o QR Code ou copie o código abaixo. O pagamento é processado pelo Stripe — confirmação automática em segundos.
                                                        </p>

                                                        <label style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#6b7280;">
                                                            Código PIX copia e cola
                                                        </label>
                                                        <div class="input-group input-group-sm mb-3" style="max-width:380px;">
                                                            <input type="text" class="form-control" id="pixCopiaCola" readonly
                                                                   style="font-size:.72rem;font-family:monospace;">
                                                            <div class="input-group-append">
                                                                <button class="btn btn-outline-secondary" type="button"
                                                                        onclick="copiarPixCode()" title="Copiar código">
                                                                    <i class="fas fa-copy" id="pixCopyIcon"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        {{-- Status do pagamento --}}
                                                        <div id="pixStatusArea">
                                                            <div id="pixAguardando" class="alert alert-warning py-2 mb-2" style="font-size:.82rem;">
                                                                <span class="spinner-border spinner-border-sm mr-1"></span>
                                                                Aguardando pagamento… <span id="pixContador" class="font-weight-bold"></span>
                                                            </div>
                                                            <div id="pixPago" class="alert alert-success py-2 mb-2" style="display:none;font-size:.82rem;">
                                                                <i class="fas fa-check-circle mr-1"></i>
                                                                <strong>Pagamento confirmado!</strong> Sua assinatura está sendo ativada…
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Estado: erro --}}
                                            <div id="pixErro" style="display:none;" class="alert alert-danger py-2" style="font-size:.83rem;">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                <span id="pixErroMsg">Não foi possível gerar o QR Code.</span>
                                                <button type="button" class="btn btn-sm btn-danger ml-2" onclick="carregarPix()">
                                                    <i class="fas fa-redo mr-1"></i> Tentar novamente
                                                </button>
                                            </div>
                                        </div>

                                        {{-- dados para JS --}}
                                        <input type="hidden" id="pixPagamentoId" value="{{ $proximoPagamento->id }}">
                                        <input type="hidden" id="pixUrlGerar"
                                               value="{{ route('assinatura.pagamento.pix', $proximoPagamento) }}">
                                        <input type="hidden" id="pixUrlStatus"
                                               value="{{ route('assinatura.pagamento.status', $proximoPagamento) }}">
                                        {{-- Se já tem QR Code salvo e não expirou, pré-popula --}}
                                        @if ($proximoPagamento->pix_qr_code_image && $proximoPagamento->pix_expira_em?->isFuture())
                                            <input type="hidden" id="pixQrPreload"
                                                   value="{{ $proximoPagamento->pix_qr_code_image }}">
                                            <input type="hidden" id="pixCodePreload"
                                                   value="{{ $proximoPagamento->pix_qr_code_text }}">
                                            <input type="hidden" id="pixExpiraPreload"
                                                   value="{{ $proximoPagamento->pix_expira_em->toIso8601String() }}">
                                        @endif
                                    @endif

                                    {{-- Falar com atendente (boleto legado ou sem método reconhecido) --}}
                                    @if (!in_array($proximoPagamento->metodo, ['pix', 'cartao']))
                                        <div>
                                            <p class="font-weight-bold mb-1" style="font-size:.88rem;">
                                                <i class="fas fa-headset mr-1"></i> Precisa de ajuda para pagar?
                                            </p>
                                            <p class="text-muted mb-3" style="font-size:.82rem;">
                                                Fale com nossa equipe e escolha a melhor forma de pagamento para você.
                                            </p>
                                            <div class="d-flex" style="gap:.75rem;flex-wrap:wrap;">
                                                <a href="mailto:suporte@decars.com.br?subject={{ urlencode('Pagamento do plano - Ref: ' . ($proximoPagamento->referencia_interna ?? '')) }}"
                                                   class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-envelope mr-1"></i> E-mail
                                                </a>
                                                <a href="https://wa.me/558192554172?text={{ urlencode('Olá! Preciso pagar minha fatura. Ref: ' . ($proximoPagamento->referencia_interna ?? '') . ' - R$ ' . number_format($proximoPagamento->valor, 2, ',', '.')) }}"
                                                   target="_blank" rel="noopener" class="btn btn-success btn-sm">
                                                    <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                                                </a>
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                        onclick="abrirAlterarMetodo({{ $proximoPagamento->id }}, '{{ $proximoPagamento->metodo }}')">
                                                    <i class="fas fa-exchange-alt mr-1"></i> Alterar forma de pagamento
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Cartão de Crédito --}}
                                    @if ($proximoPagamento->metodo === 'cartao')
                                        <div id="cartaoWrapper">
                                            {{-- Formulário de cartão --}}
                                            <div id="cartaoFormArea">
                                                <p class="font-weight-bold mb-2" style="font-size:.92rem;color:#0a1628;">
                                                    <i class="fas fa-credit-card mr-1"></i>
                                                    Pague R$ {{ number_format($proximoPagamento->valor, 2, ',', '.') }} com cartão de crédito
                                                </p>

                                                <div id="cartaoErro" class="alert alert-danger py-2 mb-2" style="display:none;font-size:.82rem;"></div>
                                                <div id="cartaoSucesso" class="alert alert-success py-2 mb-2" style="display:none;font-size:.82rem;">
                                                    <i class="fas fa-check-circle mr-1"></i> <strong>Pagamento confirmado!</strong> Sua assinatura está sendo ativada…
                                                </div>

                                                {{-- Stripe Card Element --}}
                                                <div id="stripe-card-element"
                                                     style="border:1px solid #ced4da;border-radius:6px;padding:10px 12px;background:#fff;margin-bottom:.75rem;"></div>

                                                <div class="custom-control custom-checkbox mb-3">
                                                    <input type="checkbox" class="custom-control-input" id="chkSalvarCartao">
                                                    <label class="custom-control-label" for="chkSalvarCartao" style="font-size:.82rem;">
                                                        Salvar cartão para renovações futuras
                                                    </label>
                                                </div>

                                                <button type="button" class="btn btn-primary btn-sm" id="btnPagarCartao"
                                                        onclick="pagarCartao()">
                                                    <i class="fas fa-lock mr-1"></i> Pagar agora
                                                </button>
                                                <div class="text-muted mt-2" style="font-size:.72rem;">
                                                    <i class="fas fa-shield-alt mr-1"></i> Pagamento seguro via Stripe. Seus dados não são armazenados em nossos servidores.
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" id="cartaoUrlIntent"
                                               value="{{ route('assinatura.pagamento.cartao-intent', $proximoPagamento) }}">
                                        <input type="hidden" id="cartaoUrlConfirmar"
                                               value="{{ route('assinatura.pagamento.cartao-confirmar', $proximoPagamento) }}">
                                        <input type="hidden" id="cartaoCsrf"
                                               value="{{ csrf_token() }}">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             ABA 2 — HISTÓRICO DE PAGAMENTOS
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="pane-historico" role="tabpanel">
            @if ($historico->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Nenhum pagamento registrado ainda.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" style="font-size:.85rem;">
                        <thead class="thead-light">
                            <tr>
                                <th class="pl-3">Vencimento</th>
                                <th>Plano</th>
                                <th>Período</th>
                                <th>Método</th>
                                <th class="text-right">Valor</th>
                                <th class="text-center">Status</th>
                                <th>Pago em</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($historico as $pag)
                                <tr>
                                    <td class="pl-3 align-middle">{{ $pag->data_vencimento->format('d/m/Y') }}</td>
                                    <td class="align-middle">{{ $pag->empresaPlano?->plano?->nome ?? '—' }}</td>
                                    <td class="align-middle">
                                        <span class="badge badge-light border">
                                            {{ $pag->periodicidade === 'anual' ? 'Anual' : 'Mensal' }}
                                        </span>
                                    </td>
                                    <td class="align-middle">{{ $pag->metodo_texto }}</td>
                                    <td class="align-middle text-right font-weight-bold">
                                        R$ {{ number_format($pag->valor, 2, ',', '.') }}
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge {{ $pag->status_badge['class'] }}">{{ $pag->status_badge['label'] }}</span>
                                    </td>
                                    <td class="align-middle text-muted">{{ $pag->pago_em ? $pag->pago_em->format('d/m/Y') : '—' }}</td>
                                    <td class="align-middle" style="white-space:nowrap;">
                                        @if ($pag->boleto_url)
                                            <a href="{{ $pag->boleto_url }}" target="_blank" rel="noopener"
                                               class="btn btn-xs btn-outline-secondary mr-1" title="Ver boleto">
                                                <i class="fas fa-barcode"></i>
                                            </a>
                                        @endif
                                        @if ($pag->status === 'pendente')
                                            <button type="button" class="btn btn-xs btn-outline-warning"
                                                    title="Alterar forma de pagamento"
                                                    onclick="abrirAlterarMetodo({{ $pag->id }}, '{{ $pag->metodo }}')">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($historico->hasPages())
                    <div class="mt-3">{{ $historico->links() }}</div>
                @endif
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             ABA 3 — TROCAR PLANO
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="pane-planos" role="tabpanel">
            <div class="d-flex justify-content-center mb-3 align-items-center" style="gap:.75rem;">
                <span class="font-weight-bold" id="lblMensal2"
                      style="font-size:.9rem;color:#0a1628;">Mensal</span>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="toggleAnual">
                    <label class="custom-control-label" for="toggleAnual"></label>
                </div>
                <span class="font-weight-bold text-muted" id="lblAnual2" style="font-size:.9rem;">
                    Anual <span class="badge badge-success ml-1" style="font-size:.7rem;">Economize até 20%</span>
                </span>
            </div>

            {{-- Labels dos grupos de features --}}
            @php
            $grupoLabels = [
                'Frota'    => ['icone' => 'car',           'label' => 'Frota'],
                'Reservas' => ['icone' => 'calendar-check','label' => 'Reservas'],
                'Financeiro'=> ['icone'=> 'dollar-sign',   'label' => 'Financeiro'],
                'Estacoes' => ['icone' => 'map-marker-alt','label' => 'Estacoes'],
                'Site'     => ['icone' => 'globe',         'label' => 'Site de Vendas'],
                'Modulos'  => ['icone' => 'puzzle-piece',  'label' => 'Modulos Extras'],
                'Gestao'   => ['icone' => 'users-cog',     'label' => 'Gestao'],
            ];
            @endphp

            <div class="row justify-content-center">
                @foreach ($planos as $p)
                    @php
                        $isAtual  = $assinatura && $assinatura->plano_id === $p->id && $assinatura->estaAtivo();
                        $features = $p->featuresDetalhadas();
                        $grupos   = collect($features)->groupBy('grupo');
                    @endphp
                    <div class="col-sm-6 col-xl-4 mb-4">
                        <div class="card h-100 {{ $p->destaque ? 'shadow' : '' }}"
                             style="border:{{ $isAtual ? '2px solid #22c55e' : ($p->destaque ? '2px solid #0a1628' : '1px solid #e2e8f0') }};border-radius:14px;overflow:hidden;transition:box-shadow .2s;">

                            {{-- Faixa topo --}}
                            @if ($isAtual)
                                <div class="py-1 text-center text-white" style="background:#22c55e;font-size:.7rem;font-weight:700;letter-spacing:.06em;">
                                    <i class="fas fa-check mr-1"></i> PLANO ATUAL
                                </div>
                            @elseif ($p->destaque)
                                <div class="py-1 text-center text-white" style="background:#0a1628;font-size:.7rem;font-weight:700;letter-spacing:.06em;">
                                    <i class="fas fa-star mr-1"></i> MAIS ESCOLHIDO
                                </div>
                            @endif

                            <div class="card-body p-3 d-flex flex-column">
                                {{-- Nome + preco --}}
                                <h5 class="font-weight-bold mb-0">{{ $p->nome }}</h5>
                                <p class="text-muted mb-2" style="font-size:.76rem;min-height:1.8rem;">{{ $p->descricao }}</p>

                                <div class="mb-2">
                                    <div style="font-size:2rem;font-weight:900;color:#0a1628;line-height:1;">
                                        <span class="plan-price-mensal">R$ {{ number_format($p->valor_mensal, 2, ',', '.') }}</span>
                                        <span class="plan-price-anual d-none">R$ {{ number_format($p->valor_anual, 2, ',', '.') }}</span>
                                    </div>
                                    <small class="text-muted">/mes</small>
                                    <div class="plan-anual-note d-none text-success" style="font-size:.75rem;">
                                        <i class="fas fa-piggy-bank mr-1"></i>Economia R$ {{ number_format($p->economia_anual, 2, ',', '.') }}/ano
                                    </div>
                                </div>

                                <hr class="my-2">

                                {{-- Features detalhadas por grupo --}}
                                <div class="flex-grow-1 mb-3" style="font-size:.78rem;">
                                    @foreach ($grupoLabels as $grupoKey => $grupoMeta)
                                        @if ($grupos->has($grupoKey))
                                            <div class="mb-2">
                                                <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:.3rem;">
                                                    <i class="fas fa-{{ $grupoMeta['icone'] }} mr-1"></i>{{ $grupoMeta['label'] }}
                                                </div>
                                                @foreach ($grupos[$grupoKey] as $feat)
                                                    <div class="d-flex align-items-start mb-1 {{ !$feat['incluso'] ? 'text-muted' : '' }}"
                                                         style="{{ $feat['destaque'] && $feat['incluso'] ? 'font-weight:700;' : '' }}">
                                                        <i class="fas fa-{{ $feat['incluso'] ? 'check-circle text-success' : 'times-circle text-secondary' }} mr-2 flex-shrink-0"
                                                           style="font-size:.75rem;margin-top:2px;"></i>
                                                        <div>
                                                            <span>{{ $feat['label'] }}</span>
                                                            <div class="text-muted" style="font-size:.7rem;line-height:1.35;font-weight:400;">{{ $feat['descricao'] }}</div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                {{-- Botao --}}
                                @if ($isAtual)
                                    <span class="btn btn-sm btn-block disabled"
                                          style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;cursor:default;">
                                        <i class="fas fa-check mr-1"></i> Plano Atual
                                    </span>
                                @else
                                    <a href="{{ route('assinatura.contratar', ['plano_id' => $p->id, 'periodicidade' => 'mensal']) }}"
                                       class="btn btn-sm btn-block plano-contratar-btn {{ $p->destaque ? 'btn-primary' : 'btn-outline-primary' }}"
                                       data-plano="{{ $p->id }}">
                                        <i class="fas fa-arrow-right mr-1"></i>
                                        {{ $assinatura ? 'Mudar para este plano' : 'Contratar agora' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>{{-- /tab-content --}}
@stop

@section('footer')
{{-- ══ MODAL: Alterar Método de Pagamento ══ --}}
<div class="modal fade" id="modalAlterarMetodo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAlterarMetodo" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exchange-alt mr-1"></i> Alterar Forma de Pagamento
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3" style="font-size:.875rem;">
                        Escolha a nova forma de pagamento para a cobrança pendente.
                    </p>

                    <div class="row" id="metodosCards">
                        @foreach ([
                            ['pix',    'fas fa-qrcode',      'PIX',               'Pagamento instantâneo com QR Code.'],
                            ['cartao', 'fas fa-credit-card', 'Cartão de Crédito', 'Pague com cartão de crédito.'],
                        ] as [$v, $ic, $nm, $desc])
                            <div class="col-md-4 mb-2">
                                <label class="metodo-alt-opt card border text-center p-3 h-100"
                                       style="cursor:pointer;margin-bottom:0;transition:border-color .15s,background .15s;"
                                       for="met_{{ $v }}" onclick="onAlterarMetodoChange('{{ $v }}')">
                                    <input type="radio" name="metodo" value="{{ $v }}" id="met_{{ $v }}"
                                           class="d-none">
                                    <div style="font-size:1.5rem;color:#0a1628;margin-bottom:.4rem;">
                                        <i class="{{ $ic }}"></i>
                                    </div>
                                    <div class="font-weight-bold" style="font-size:.83rem;">{{ $nm }}</div>
                                    <div class="text-muted" style="font-size:.73rem;margin-top:.2rem;">{{ $desc }}</div>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <input type="hidden" name="renovacao_automatica" value="0">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check mr-1"></i> Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══ MODAL: Renovar Plano ══ --}}
@if ($assinatura)
<div class="modal fade" id="modalRenovar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('assinatura.renovar') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-sync-alt mr-1"></i> Renovar Plano</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3" style="font-size:.875rem;">
                        <i class="fas fa-info-circle mr-1"></i>
                        Renovando o plano <strong>{{ $assinatura->plano->nome }}</strong>.
                    </div>

                    {{-- Periodicidade --}}
                    <div class="form-group">
                        <label class="font-weight-bold mb-2">Periodicidade</label>
                        <div class="d-flex" style="gap:.75rem;">
                            @foreach (['mensal' => ['Mensal', $assinatura->plano->valor_mensal, null], 'anual' => ['Anual', $assinatura->plano->valor_anual, $assinatura->plano->desconto_anual_percentual]] as $val => [$nm, $vl, $desc])
                                <label class="card border p-3 flex-fill renovar-periodo-opt"
                                       style="cursor:pointer;margin-bottom:0;" for="rp_{{ $val }}">
                                    <div class="d-flex align-items-center" style="gap:.4rem;">
                                        <input type="radio" name="periodicidade" value="{{ $val }}" id="rp_{{ $val }}"
                                               class="mr-1" {{ $val === 'mensal' ? 'checked' : '' }}>
                                        <div>
                                            <div class="font-weight-bold" style="font-size:.83rem;">
                                                {{ $nm }}
                                                @if ($desc > 0)
                                                    <span class="badge badge-success ml-1" style="font-size:.65rem;">{{ $desc }}% off</span>
                                                @endif
                                            </div>
                                            <div style="font-size:1.1rem;font-weight:900;color:#0a1628;">
                                                R$ {{ number_format($vl, 2, ',', '.') }}
                                            </div>
                                            <div class="text-muted" style="font-size:.72rem;">/mês{{ $val === 'anual' ? ' · cobrado anualmente' : '' }}</div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Método --}}
                    <div class="form-group mb-2">
                        <label class="font-weight-bold mb-2">Método de Pagamento</label>
                        <div class="row">
                            @foreach ([
                                ['pix',    'fas fa-qrcode',      'PIX'],
                                ['cartao', 'fas fa-credit-card', 'Cartão de Crédito'],
                            ] as [$v, $ic, $nm])
                                <div class="col-4">
                                    <label class="renovar-metodo-opt card border text-center p-2"
                                           style="cursor:pointer;margin-bottom:0;" for="rm_{{ $v }}"
                                           onclick="onRenovarMetodoChange('{{ $v }}')">
                                        <input type="radio" name="metodo_cobranca" value="{{ $v }}" id="rm_{{ $v }}"
                                               class="d-none"
                                               {{ ($assinatura->metodo_cobranca ?? '') === $v ? 'checked' : '' }}>
                                        <i class="{{ $ic }}" style="font-size:1.3rem;color:#0a1628;display:block;margin-bottom:.3rem;"></i>
                                        <span style="font-size:.78rem;font-weight:600;">{{ $nm }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <input type="hidden" name="renovacao_automatica" id="renovarRenovacaoAuto" value="0">

                    <div id="divRenovarCartao" class="p-2 rounded mt-2"
                         style="display:none;background:#f0f9ff;border:1px solid #bae6fd;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="font-weight-bold" style="font-size:.82rem;">
                                    <i class="fas fa-sync-alt mr-1 text-primary"></i> Renovação automática
                                </div>
                                <div class="text-muted" style="font-size:.73rem;">Renova sem precisar pagar novamente.</div>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="renovarToggleAuto"
                                       onchange="document.getElementById('renovarRenovacaoAuto').value = this.checked ? '1' : '0'">
                                <label class="custom-control-label" for="renovarToggleAuto"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt mr-1"></i> Confirmar Renovação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('css')
<style>
.nav-tabs .nav-link { border-radius:0; border-bottom:none; }
.nav-tabs .nav-link.active { border-color:#0a1628 #0a1628 #fff; color:#0a1628 !important; }
.nav-tabs .nav-link:hover { color:#0a1628 !important; }

.metodo-alt-opt.selected,
.renovar-metodo-opt.selected {
    border-color:#0a1628 !important;
    background:#eef2ff !important;
}
.renovar-periodo-opt.selected { border-color:#0a1628 !important; background:#f8faff !important; }
</style>
@endpush

@push('js')
@if(isset($proximoPagamento) && $proximoPagamento?->metodo === 'cartao')
<script src="https://js.stripe.com/v3/"></script>
<script>
// ── Stripe Elements (cartão) ─────────────────────────────────────────────────
let stripeInstance = null;
let cardElement    = null;
let cartaoClientSecret = null;

async function inicializarStripeElements(stripeKey) {
    stripeInstance = Stripe(stripeKey);
    const elements = stripeInstance.elements();
    cardElement = elements.create('card', {
        style: {
            base: { fontSize: '14px', color: '#212529', '::placeholder': { color: '#6c757d' } },
            invalid: { color: '#dc3545' },
        },
        hidePostalCode: true,
    });
    cardElement.mount('#stripe-card-element');
    cardElement.on('change', ({ error }) => {
        const el = document.getElementById('cartaoErro');
        if (error) { el.textContent = error.message; el.style.display = 'block'; }
        else el.style.display = 'none';
    });
}

async function pagarCartao() {
    const btn = document.getElementById('btnPagarCartao');
    const erroEl = document.getElementById('cartaoErro');
    const salvar = document.getElementById('chkSalvarCartao')?.checked ?? false;
    const urlIntent    = document.getElementById('cartaoUrlIntent')?.value;
    const urlConfirmar = document.getElementById('cartaoUrlConfirmar')?.value;
    const csrf         = document.getElementById('cartaoCsrf')?.value;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Processando…';
    erroEl.style.display = 'none';

    try {
        // 1. Obtém o client_secret do servidor
        if (!cartaoClientSecret) {
            const r = await fetch(urlIntent, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ salvar_cartao: salvar }),
            });
            const data = await r.json();
            if (data.error) throw new Error(data.error);
            cartaoClientSecret = data.client_secret;
        }

        // 2. Confirma o pagamento com os dados do cartão
        const { paymentIntent, error } = await stripeInstance.confirmCardPayment(cartaoClientSecret, {
            payment_method: { card: cardElement },
        });

        if (error) throw new Error(error.message);

        if (paymentIntent.status !== 'succeeded') {
            throw new Error('Pagamento não aprovado. Por favor, tente novamente.');
        }

        // 3. Notifica o servidor
        const r2 = await fetch(urlConfirmar, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ payment_intent_id: paymentIntent.id }),
        });
        const data2 = await r2.json();
        if (data2.error) throw new Error(data2.error);

        // Sucesso
        document.getElementById('cartaoSucesso').style.display = 'block';
        document.getElementById('cartaoFormArea').querySelector('button').style.display = 'none';
        setTimeout(() => location.reload(), 2500);

    } catch (err) {
        erroEl.textContent = err.message || 'Erro ao processar pagamento.';
        erroEl.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock mr-1"></i> Pagar agora';
        cartaoClientSecret = null; // reseta para nova tentativa
    }
}

// Inicializa Elements buscando a stripe_key do servidor
document.addEventListener('DOMContentLoaded', async () => {
    if (!document.getElementById('stripe-card-element')) return;
    const urlIntent = document.getElementById('cartaoUrlIntent')?.value;
    const csrf      = document.getElementById('cartaoCsrf')?.value;
    // Busca só a stripe_key sem criar o intent ainda
    const r = await fetch(urlIntent + '?peek=1', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ peek: true }),
    });
    const data = await r.json();
    if (data.stripe_key) await inicializarStripeElements(data.stripe_key);
});
</script>
@endif
<script>
// ── Navegação entre abas ─────────────────────────────────────────────────────
function irParaAba(tabId) {
    $('#' + tabId).tab('show');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── PIX via Stripe ────────────────────────────────────────────────────────────
let pixPollingTimer = null;

function mostrarPixConteudo(qrImage, qrText, expiraEm) {
    document.getElementById('pixLoading').style.display  = 'none';
    document.getElementById('pixErro').style.display     = 'none';
    document.getElementById('pixConteudo').style.display = 'block';

    document.getElementById('pixQrImage').src = qrImage;
    document.getElementById('pixCopiaCola').value = qrText;

    // Contador de expiração
    if (expiraEm) {
        const expira = new Date(expiraEm);
        const tick = () => {
            const diff = Math.max(0, Math.floor((expira - Date.now()) / 1000));
            const h = String(Math.floor(diff / 3600)).padStart(2, '0');
            const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
            const s = String(diff % 60).padStart(2, '0');
            const el = document.getElementById('pixExpira');
            if (el) el.textContent = diff > 0 ? `Expira em ${h}:${m}:${s}` : 'QR Code expirado';
        };
        tick();
        setInterval(tick, 1000);
    }

    iniciarPolling();
}

function carregarPix() {
    const urlGerar  = document.getElementById('pixUrlGerar')?.value;
    const qrPreload = document.getElementById('pixQrPreload')?.value;
    const codePreload = document.getElementById('pixCodePreload')?.value;
    const expiraPreload = document.getElementById('pixExpiraPreload')?.value;

    if (!urlGerar) return;

    // Se já tem QR salvo e válido, usa direto sem nova chamada ao Stripe
    if (qrPreload && codePreload) {
        mostrarPixConteudo(qrPreload, codePreload, expiraPreload);
        return;
    }

    document.getElementById('pixLoading').style.display  = 'block';
    document.getElementById('pixConteudo').style.display = 'none';
    document.getElementById('pixErro').style.display     = 'none';

    fetch(urlGerar, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) throw new Error(data.error);
        mostrarPixConteudo(data.qr_image, data.qr_text, data.expira_em);
    })
    .catch(err => {
        document.getElementById('pixLoading').style.display = 'none';
        document.getElementById('pixErroMsg').textContent   = err.message || 'Erro ao gerar QR Code.';
        document.getElementById('pixErro').style.display    = 'block';
    });
}

function iniciarPolling() {
    const urlStatus = document.getElementById('pixUrlStatus')?.value;
    if (!urlStatus) return;

    pixPollingTimer = setInterval(() => {
        fetch(urlStatus, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.pago) {
                    clearInterval(pixPollingTimer);
                    document.getElementById('pixAguardando').style.display = 'none';
                    document.getElementById('pixPago').style.display       = 'block';
                    // Redireciona após 2s para atualizar a página com o novo status
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .catch(() => {}); // silencia erros de rede no polling
    }, 4000); // verifica a cada 4 segundos
}

function copiarPixCode() {
    const el = document.getElementById('pixCopiaCola');
    if (!el) return;
    navigator.clipboard.writeText(el.value).then(() => {
        const ic = document.getElementById('pixCopyIcon');
        if (ic) { ic.className = 'fas fa-check text-success'; setTimeout(() => ic.className = 'fas fa-copy', 2000); }
    });
}

function copiarCodigo() {
    const el = document.getElementById('codigoBoleto');
    if (!el) return;
    navigator.clipboard.writeText(el.value).then(() => {
        const ic = document.getElementById('boletoCopyIcon');
        if (ic) { ic.className = 'fas fa-check text-success'; setTimeout(() => ic.className = 'fas fa-copy', 1800); }
    });
}

// Inicia automaticamente ao carregar se tiver pagamento PIX pendente
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('pixWrapper')) carregarPix();
});

// ── Toggle mensal/anual na aba Trocar Plano ───────────────────────────────────
document.getElementById('toggleAnual')?.addEventListener('change', function () {
    const anual = this.checked;
    document.querySelectorAll('.plan-price-mensal').forEach(el => el.classList.toggle('d-none', anual));
    document.querySelectorAll('.plan-price-anual').forEach(el => el.classList.toggle('d-none', !anual));
    document.querySelectorAll('.plan-anual-note').forEach(el => el.classList.toggle('d-none', !anual));
    const periodo = anual ? 'anual' : 'mensal';
    document.getElementById('lblMensal2').style.color = anual ? '#94a3b8' : '#0a1628';
    document.getElementById('lblAnual2').style.color  = anual ? '#0a1628' : '#94a3b8';
    document.querySelectorAll('.plano-contratar-btn').forEach(btn => {
        const id = btn.dataset.plano;
        btn.href = `{{ url('/admin/meu-plano/contratar') }}?plano_id=${id}&periodicidade=${periodo}`;
    });
});

// ── Modal Alterar Método ──────────────────────────────────────────────────────
function abrirAlterarMetodo(pagamentoId, metodoAtual) {
    const form = document.getElementById('formAlterarMetodo');
    form.action = `/admin/meu-plano/pagamento/${pagamentoId}/metodo`;
    document.querySelectorAll('#metodosCards .metodo-alt-opt').forEach(l => l.classList.remove('selected'));
    const radio = document.getElementById('met_' + metodoAtual);
    if (radio) {
        radio.checked = true;
        radio.closest('.metodo-alt-opt')?.classList.add('selected');
    }
    $('#modalAlterarMetodo').modal('show');
}

function onAlterarMetodoChange(metodo) {
    document.querySelectorAll('.metodo-alt-opt').forEach(l => l.classList.remove('selected'));
    document.getElementById('met_' + metodo)?.closest('.metodo-alt-opt')?.classList.add('selected');
}

// ── Modal Renovar ─────────────────────────────────────────────────────────────
function onRenovarMetodoChange(metodo) {
    document.querySelectorAll('.renovar-metodo-opt').forEach(l => l.classList.remove('selected'));
    document.getElementById('rm_' + metodo)?.closest('.renovar-metodo-opt')?.classList.add('selected');
    document.getElementById('divRenovarCartao').style.display = metodo === 'cartao' ? 'block' : 'none';
    if (metodo !== 'cartao') {
        document.getElementById('renovarToggleAuto').checked = false;
        document.getElementById('renovarRenovacaoAuto').value = '0';
    }
}

// Inicializa seleção visual já marcada nos radios
document.querySelectorAll('.renovar-metodo-opt input:checked').forEach(r =>
    r.closest('.renovar-metodo-opt')?.classList.add('selected')
);
document.querySelectorAll('.renovar-periodo-opt input:checked').forEach(r =>
    r.closest('.renovar-periodo-opt')?.classList.add('selected')
);
document.querySelectorAll('.renovar-periodo-opt').forEach(l =>
    l.addEventListener('click', () => {
        document.querySelectorAll('.renovar-periodo-opt').forEach(x => x.classList.remove('selected'));
        l.classList.add('selected');
    })
);
// Inicializa painel cartão no modal renovar
(function() {
    const checked = document.querySelector('.renovar-metodo-opt input:checked');
    if (checked?.value === 'cartao') document.getElementById('divRenovarCartao').style.display = 'block';
})();
</script>
@endpush
