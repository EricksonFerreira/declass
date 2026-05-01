@extends('adminlte::page')

@section('title', 'Contratar — ' . $plano->nome)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
        <div>
            <h1 class="m-0 text-dark" style="font-size:1.3rem;font-weight:700;">
                Contratar: <span style="color:#0a1628;">{{ $plano->nome }}</span>
            </h1>
            <small class="text-muted">Confirme as condicoes e escolha a forma de pagamento</small>
        </div>
        <a href="{{ route('assinatura.meu-plano') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if ($erroDowngrade)
        <div class="alert alert-warning">
            <i class="fas fa-lock mr-1"></i> <strong>Downgrade bloqueado:</strong> {{ $erroDowngrade }}
        </div>
    @endif

    @php
        $veiculosExcedentes = $plano->limite_veiculos !== null && $totalVeiculos > $plano->limite_veiculos
            ? $totalVeiculos - $plano->limite_veiculos : 0;
    @endphp
    @if ($veiculosExcedentes > 0)
        <div class="alert alert-warning">
            <i class="fas fa-car mr-1"></i>
            <strong>Atencao:</strong> Voce tem <strong>{{ $totalVeiculos }} veiculos ativos</strong> e este plano permite apenas <strong>{{ $plano->limite_veiculos }}</strong>. Ao contratar, voce precisara desativar <strong>{{ $veiculosExcedentes }} veiculo(s)</strong>.
        </div>
    @endif

    {{-- Stepper --}}
    <div class="card card-outline card-secondary mb-3" style="border-radius:12px;">
        <div class="card-body py-2">
            <div class="plano-stepper">
                <div class="plano-step is-active" id="stepIndicator1">
                    <div class="plano-step-circle"><i class="fas fa-tags"></i></div>
                    <div class="plano-step-text">
                        <div class="plano-step-title">Plano</div>
                        <div class="plano-step-sub">Escolha mensal/anual</div>
                    </div>
                </div>
                <div class="plano-step-line"></div>
                <div class="plano-step" id="stepIndicator2">
                    <div class="plano-step-circle"><i class="fas fa-credit-card"></i></div>
                    <div class="plano-step-text">
                        <div class="plano-step-title">Pagamento</div>
                        <div class="plano-step-sub">PIX ou cartao</div>
                    </div>
                </div>
                <div class="plano-step-line"></div>
                <div class="plano-step" id="stepIndicator3">
                    <div class="plano-step-circle"><i class="fas fa-check"></i></div>
                    <div class="plano-step-text">
                        <div class="plano-step-title">Ativacao</div>
                        <div class="plano-step-sub">Liberacao do plano</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('assinatura.contratar.confirmar') }}" method="POST" id="formContratar">
        @csrf
        <input type="hidden" name="plano_id" value="{{ $plano->id }}">
        <input type="hidden" name="periodicidade" id="inputPeriodicidade" value="{{ $periodicidade }}">
        <input type="hidden" name="metodo_pagamento" id="inputMetodo" value="{{ old('metodo_pagamento') }}">
        <input type="hidden" name="renovacao_automatica" id="inputRenovacaoAutomatica" value="0">

        <div class="row">

            {{-- ── COLUNA ESQUERDA: resumo do plano ── --}}
            <div class="col-lg-5 mb-3">
                <div class="card h-100" style="border:2px solid #0a1628;border-radius:12px;overflow:hidden;">
                    <div class="card-header text-white py-2" style="background:#0a1628;">
                        <h5 class="mb-0" style="font-size:.95rem;">
                            <i class="fas fa-tags mr-1"></i> {{ $plano->nome }}
                            @if ($plano->destaque)
                                <span class="badge badge-warning ml-2" style="font-size:.68rem;">
                                    <i class="fas fa-star mr-1"></i> Mais escolhido
                                </span>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3" style="font-size:.85rem;">{{ $plano->descricao }}</p>

                        <div class="d-flex align-items-center mb-3" style="gap:.6rem;">
                            <span id="lblMensal" style="font-size:.875rem;font-weight:{{ $periodicidade === 'mensal' ? '700' : '400' }};color:{{ $periodicidade === 'mensal' ? '#0a1628' : '#94a3b8' }};">Mensal</span>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="togglePeriodo"
                                       {{ $periodicidade === 'anual' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="togglePeriodo"></label>
                            </div>
                            <span id="lblAnual" style="font-size:.875rem;font-weight:{{ $periodicidade === 'anual' ? '700' : '400' }};color:{{ $periodicidade === 'anual' ? '#0a1628' : '#94a3b8' }};">
                                Anual
                                @if ($plano->desconto_anual_percentual > 0)
                                    <span class="badge badge-success ml-1" style="font-size:.68rem;">{{ $plano->desconto_anual_percentual }}% off</span>
                                @endif
                            </span>
                        </div>

                        <div class="d-flex align-items-end mb-1" style="gap:.4rem;">
                            <div style="font-size:2.5rem;font-weight:900;color:#0a1628;line-height:1;">
                                R$ <span id="valorExibido">
                                    {{ $periodicidade === 'anual'
                                        ? number_format($plano->valor_anual, 2, ',', '.')
                                        : number_format($plano->valor_mensal, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="text-muted mb-1" style="font-size:.875rem;">/mes</div>
                        </div>
                        <div id="notaAnual" class="text-success mb-3" style="font-size:.8rem;{{ $periodicidade !== 'anual' ? 'display:none;' : '' }}">
                            <i class="fas fa-check-circle mr-1"></i>
                            Cobrado anualmente · Economia de R$ {{ number_format($plano->economia_anual, 2, ',', '.') }}/ano
                        </div>

                        <hr class="my-2">
                        <ul class="list-unstyled mb-0" style="font-size:.83rem;">
                            @foreach ($plano->itens as $item)
                                <li class="mb-1">
                                    <i class="fas fa-check text-success mr-1" style="font-size:.7rem;"></i>
                                    {{ $item->descricao }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            {{-- ── COLUNA DIREITA ── --}}
            <div class="col-lg-7 mb-3">

                {{-- ── STEP 1: Escolher metodo ── --}}
                <div id="stepMetodo">
                    <div class="card mb-3" style="border-radius:12px;overflow:hidden;">
                        <div class="card-header py-2" style="background:#f8fafc;">
                            <h5 class="mb-0" style="font-size:.9rem;font-weight:700;">
                                <span class="badge badge-primary mr-2">1</span> Forma de Pagamento
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row" id="metodosGrid">
                                @foreach ([
                                    ['pix',    'fas fa-qrcode',      'PIX',               'Pagamento instantaneo via QR Code. Ativacao imediata apos confirmacao.'],
                                    ['cartao', 'fas fa-credit-card', 'Cartao de Credito',  'Pague com cartao de credito. Possibilidade de renovacao automatica.'],
                                    ['atendente', 'fas fa-headset',  'Falar com Atendente', 'Prefere ajuda? Fale com nossa equipe por e-mail ou WhatsApp.'],
                                ] as [$val, $icon, $nome, $desc])
                                    <div class="col-md-4 mb-2">
                                        <div class="metodo-card card border text-center p-3 h-100"
                                             data-metodo="{{ $val }}"
                                             onclick="selecionarMetodo('{{ $val }}', this)"
                                             style="cursor:pointer;transition:border-color .15s,box-shadow .15s,background .15s;border-radius:10px;">
                                            <div style="font-size:1.8rem;color:#0a1628;margin-bottom:.5rem;">
                                                <i class="{{ $icon }}"></i>
                                            </div>
                                            <div style="font-weight:700;font-size:.85rem;color:#0a1628;">{{ $nome }}</div>
                                            <div class="text-muted mt-1" style="font-size:.73rem;line-height:1.4;">{{ $desc }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div id="errMetodo" class="alert alert-danger py-2 mt-2 mb-0" style="display:none;font-size:.875rem;">
                                <i class="fas fa-exclamation-circle mr-1"></i> Escolha uma forma de pagamento.
                            </div>
                        </div>
                    </div>

                    {{-- Painel cartao info --}}
                    <div id="divCartao" class="card mb-3" style="display:none;border:1.5px solid #bae6fd;border-radius:12px;overflow:hidden;">
                        <div class="card-header py-2" style="background:#f0f9ff;">
                            <h5 class="mb-0" style="font-size:.88rem;font-weight:700;color:#0369a1;">
                                <i class="fas fa-credit-card mr-1"></i> Opcoes do Cartao de Credito
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start" style="gap:1rem;">
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold mb-1" style="font-size:.88rem;">
                                        <i class="fas fa-sync-alt mr-1 text-primary"></i> Renovacao Automatica
                                    </div>
                                    <p class="text-muted mb-0" style="font-size:.8rem;">
                                        Ative para nunca perder o acesso. A assinatura e renovada automaticamente a cada periodo. Voce pode cancelar a qualquer momento.
                                    </p>
                                </div>
                                <div class="custom-control custom-switch flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input" id="toggleRenovacao"
                                           onchange="onToggleRenovacao(this.checked)">
                                    <label class="custom-control-label" for="toggleRenovacao"></label>
                                </div>
                            </div>
                            <div id="divRenovacaoAtiva" class="mt-2 alert alert-success py-2 mb-0" style="display:none;font-size:.8rem;">
                                <i class="fas fa-check-circle mr-1"></i>
                                <strong>Renovacao automatica ativa.</strong> O pagamento sera processado automaticamente via Stripe.
                            </div>
                            <div id="divRenovacaoInativa" class="mt-2 alert alert-info py-2 mb-0" style="font-size:.8rem;">
                                <i class="fas fa-info-circle mr-1"></i>
                                Pagamento avulso. Apos confirmar voce sera direcionado para inserir os dados do cartao.
                            </div>
                        </div>
                    </div>

                    {{-- Painel atendente --}}
                    <div id="divAtendente" class="card mb-3" style="display:none;border:1.5px solid #d1fae5;border-radius:12px;overflow:hidden;">
                        <div class="card-header py-2" style="background:#f0fdf4;">
                            <h5 class="mb-0" style="font-size:.88rem;font-weight:700;color:#065f46;">
                                <i class="fas fa-headset mr-1"></i> Fale com nossa equipe
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3" style="font-size:.83rem;">
                                Prefere que um atendente te ajude? Escolha o canal abaixo e informe o plano que deseja contratar.
                            </p>
                            <div class="d-flex" style="gap:.75rem;flex-wrap:wrap;">
                                <a href="mailto:suporte@decars.com.br?subject={{ urlencode('Quero contratar o plano ' . $plano->nome) }}"
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-envelope mr-1"></i> Enviar e-mail
                                </a>
                                <a href="https://wa.me/558192554172?text={{ urlencode('Ola! Quero contratar o plano ' . $plano->nome . '. Podem me ajudar?') }}"
                                   target="_blank" rel="noopener" class="btn btn-success btn-sm">
                                    <i class="fab fa-whatsapp mr-1"></i> Chamar no WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 2: Revisao e Pagamento ── --}}
                <div id="stepPagamento" style="display:none;">
                    <div class="card mb-3" style="border-radius:12px;overflow:hidden;">
                        <div class="card-header py-2" style="background:#f8fafc;">
                            <h5 class="mb-0" style="font-size:.9rem;font-weight:700;">
                                <span class="badge badge-primary mr-2">2</span> Revisao e Pagamento
                            </h5>
                        </div>
                        <div class="card-body">
                            {{-- Resumo antes de confirmar --}}
                            <div id="divResumoConfirmar">
                                <div class="resumo-box mb-3">
                                    <div>
                                        <div class="resumo-plano">{{ $plano->nome }}</div>
                                        <div class="resumo-sub" id="resumoPeriodicidade">{{ $periodicidade === 'anual' ? 'Anual' : 'Mensal' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="resumo-valor">R$ <span id="resumoValor">{{ $periodicidade === 'anual' ? number_format($plano->valor_anual, 2, ',', '.') : number_format($plano->valor_mensal, 2, ',', '.') }}</span></div>
                                        <div class="resumo-sub" id="resumoMetodo"></div>
                                    </div>
                                </div>
                                <p class="text-muted mb-0" style="font-size:.82rem;">
                                    <i class="fas fa-shield-alt text-primary mr-1"></i>
                                    Sua conta ficara em modo de avaliacao ate a confirmacao do pagamento.
                                </p>
                            </div>

                            {{-- PIX apos confirmar --}}
                            <div id="divPagamentoPix" style="display:none;">
                                <div id="pixLoading" class="text-center py-4">
                                    <div class="spinner-border text-primary mb-2" style="width:2rem;height:2rem;"></div>
                                    <p class="text-muted mb-0" style="font-size:.85rem;">Gerando QR Code PIX...</p>
                                </div>
                                <div id="pixConteudo" style="display:none;">
                                    <div class="d-flex align-items-start" style="gap:1.5rem;flex-wrap:wrap;">
                                        <div class="text-center flex-shrink-0">
                                            <div style="padding:8px;border:2px solid #e2e8f0;border-radius:10px;background:#fff;display:inline-block;">
                                                <img id="pixQrImage" src="" alt="QR Code PIX" style="width:160px;height:160px;display:block;">
                                            </div>
                                            <div class="text-muted mt-1" style="font-size:.72rem;">Escaneie com seu banco</div>
                                            <div id="pixExpira" class="mt-1" style="font-size:.72rem;color:#dc2626;font-weight:600;"></div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="font-weight-bold mb-1" style="font-size:.92rem;color:#064e3b;">
                                                <i class="fas fa-qrcode mr-1"></i> Pague <span id="pixValorExibido"></span> via PIX
                                            </p>
                                            <p class="text-muted mb-2" style="font-size:.82rem;">
                                                Escaneie o QR Code ou copie o codigo abaixo. O pagamento e processado pelo Stripe — confirmacao automatica em segundos.
                                            </p>
                                            <label style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#6b7280;">Codigo PIX copia e cola</label>
                                            <div class="input-group input-group-sm mb-2">
                                                <input type="text" class="form-control" id="pixCopiaCola" readonly style="font-size:.72rem;font-family:monospace;background:#f8fafc;">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="copiarPixCode()" title="Copiar">
                                                        <i class="fas fa-copy" id="pixCopyIcon"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="pixAguardando" style="font-size:.8rem;color:#64748b;">
                                                <i class="fas fa-spinner fa-spin mr-1"></i> Aguardando confirmacao...
                                            </div>
                                            <div id="pixPago" class="alert alert-success py-2 mb-0" style="display:none;font-size:.82rem;">
                                                <i class="fas fa-check-circle mr-1"></i> <strong>Pagamento confirmado!</strong> Redirecionando...
                                            </div>
                                            <div id="pixErro" class="alert alert-danger py-2 mb-0" style="display:none;font-size:.82rem;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Cartao apos confirmar --}}
                            <div id="divPagamentoCartao" style="display:none;">
                                <p class="font-weight-bold mb-2" style="font-size:.88rem;"><i class="fas fa-credit-card mr-1"></i> Dados do Cartao</p>
                                <div id="card-element" style="border:1.5px solid #e2e8f0;border-radius:10px;padding:.75rem;background:#f8fafc;min-height:40px;"></div>
                                <div id="card-errors" class="mt-1" style="color:#ef4444;font-size:.8rem;display:none;"></div>
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="salvarCartao">
                                    <label class="custom-control-label" for="salvarCartao" style="font-size:.82rem;">Salvar cartao para pagamentos futuros</label>
                                </div>
                                <div id="cartaoProcessando" class="text-center py-3" style="display:none;">
                                    <div class="spinner-border text-primary mb-2" style="width:2rem;height:2rem;"></div>
                                    <p class="text-muted mb-0" style="font-size:.85rem;">Processando pagamento...</p>
                                </div>
                                <div id="cartaoSucesso" class="alert alert-success py-2 mt-2" style="display:none;font-size:.82rem;">
                                    <i class="fas fa-check-circle mr-1"></i> <strong>Pagamento confirmado!</strong> Redirecionando...
                                </div>
                                <div id="cartaoErro" class="alert alert-danger py-2 mt-2" style="display:none;font-size:.82rem;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Nota de seguranca --}}
                <div id="divNotaSeguranca" class="alert alert-light border mb-3" style="font-size:.82rem;">
                    <i class="fas fa-shield-alt text-primary mr-1"></i>
                    <strong>Sem cobranca imediata.</strong>
                    Apos confirmar, voce insere os dados de pagamento. Sua conta fica ativa em modo de avaliacao ate a confirmacao.
                </div>

                {{-- Botoes --}}
                <div id="divBtnStep1" class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('assinatura.meu-plano') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Cancelar
                    </a>
                    <button type="button" class="btn-action btn-primary" id="btnAvancarPagamento"
                            {{ $erroDowngrade ? 'disabled' : '' }}>
                        Continuar <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
                <div id="divBtnStep2" class="d-flex justify-content-between align-items-center" style="display:none;">
                    <button type="button" class="btn btn-secondary" onclick="voltarStep1()">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </button>
                    <button type="button" class="btn-action btn-action-success" id="btnConfirmarPagamento">
                        <i class="fas fa-flag-checkered mr-1"></i> Confirmar Contratacao
                    </button>
                </div>
            </div>

        </div>
    </form>
@stop

@push('css')
<style>
.plano-stepper{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap}
.plano-step{display:flex;align-items:center;gap:.6rem}
.plano-step-line{height:2px;flex:1;min-width:24px;background:#e5e7eb}
.plano-step-circle{width:34px;height:34px;border-radius:999px;display:flex;align-items:center;justify-content:center;background:#f1f5f9;border:1px solid #e2e8f0;color:#0a1628}
.plano-step.is-active .plano-step-circle{background:#0a1628;border-color:#0a1628;color:#fff}
.plano-step.is-done .plano-step-circle{background:#22c55e;border-color:#22c55e;color:#fff}
.plano-step.is-done .plano-step-line{background:#22c55e}
.plano-step-title{font-size:.82rem;font-weight:800;line-height:1}
.plano-step-sub{font-size:.72rem;color:#64748b;line-height:1.1;margin-top:2px}
#card-element.StripeElement--focus{border-color:#0a1628;box-shadow:0 0 0 3px rgba(10,22,40,.08)}
.resumo-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:1rem 1.2rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem}
.resumo-plano{font-weight:700;font-size:.95rem;color:#0f172a}
.resumo-valor{font-size:1.4rem;font-weight:900;color:#0a1628}
.resumo-sub{font-size:.78rem;color:#64748b}
</style>
@endpush

@push('js')
<script src="https://js.stripe.com/v3/"></script>
<script>
var CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
var RESERVA_ID = null;
var PAGAMENTO_ID = null;
var stripe = null;
var cartaoClientSecret = null;

var valorMensal = '{{ number_format($plano->valor_mensal, 2, ",", ".") }}';
var valorAnual  = '{{ number_format($plano->valor_anual, 2, ",", ".") }}';

document.getElementById('togglePeriodo').addEventListener('change', function () {
    var anual = this.checked;
    document.getElementById('inputPeriodicidade').value = anual ? 'anual' : 'mensal';
    document.getElementById('valorExibido').textContent  = anual ? valorAnual : valorMensal;
    document.getElementById('notaAnual').style.display   = anual ? 'block' : 'none';
    document.getElementById('lblMensal').style.fontWeight = anual ? '400' : '700';
    document.getElementById('lblMensal').style.color      = anual ? '#94a3b8' : '#0a1628';
    document.getElementById('lblAnual').style.fontWeight  = anual ? '700' : '400';
    document.getElementById('lblAnual').style.color       = anual ? '#0a1628' : '#94a3b8';
});

function selecionarMetodo(metodo, el) {
    document.querySelectorAll('.metodo-card').forEach(function (c) {
        c.style.borderColor = '#dee2e6';
        c.style.boxShadow   = 'none';
        c.style.background  = '#fff';
    });
    el.style.borderColor = '#0a1628';
    el.style.boxShadow   = '0 0 0 3px rgba(10,22,40,.1)';
    el.style.background  = '#f8faff';
    document.getElementById('inputMetodo').value = metodo === 'atendente' ? '' : metodo;
    document.getElementById('errMetodo').style.display = 'none';
    document.getElementById('divCartao').style.display    = metodo === 'cartao'    ? 'block' : 'none';
    document.getElementById('divAtendente').style.display = metodo === 'atendente' ? 'block' : 'none';
    document.getElementById('divNotaSeguranca').style.display = metodo === 'atendente' ? 'none' : 'block';
    document.getElementById('divBtnStep1').style.display  = metodo === 'atendente' ? 'none' : 'flex';
    if (metodo !== 'cartao') {
        document.getElementById('toggleRenovacao').checked = false;
        document.getElementById('inputRenovacaoAutomatica').value = '0';
        document.getElementById('divRenovacaoAtiva').style.display  = 'none';
        document.getElementById('divRenovacaoInativa').style.display = 'block';
    }
}

function onToggleRenovacao(checked) {
    document.getElementById('inputRenovacaoAutomatica').value = checked ? '1' : '0';
    document.getElementById('divRenovacaoAtiva').style.display  = checked ? 'block' : 'none';
    document.getElementById('divRenovacaoInativa').style.display = checked ? 'none'  : 'block';
}

function atualizarStepper(passo) {
    for (var i = 1; i <= 3; i++) {
        var el = document.getElementById('stepIndicator' + i);
        el.classList.remove('is-active', 'is-done');
        if (i < passo) el.classList.add('is-done');
        else if (i === passo) el.classList.add('is-active');
    }
}

function voltarStep1() {
    document.getElementById('stepMetodo').style.display = 'block';
    document.getElementById('stepPagamento').style.display = 'none';
    document.getElementById('divBtnStep1').style.display = 'flex';
    document.getElementById('divBtnStep2').style.display = 'none';
    document.getElementById('divNotaSeguranca').style.display = 'block';
    atualizarStepper(1);
}

// ── Avancar para step de revisao (sem submit) ──
document.getElementById('btnAvancarPagamento').addEventListener('click', function () {
    var metodo = document.getElementById('inputMetodo').value;
    if (!metodo) {
        document.getElementById('errMetodo').style.display = 'block';
        document.getElementById('errMetodo').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    var nomeMetodo = { pix: 'PIX', cartao: 'Cartao de Credito', atendente: 'Atendente' }[metodo] || metodo;
    document.getElementById('resumoMetodo').textContent = nomeMetodo;

    document.getElementById('stepMetodo').style.display = 'none';
    document.getElementById('stepPagamento').style.display = 'block';
    document.getElementById('divBtnStep1').style.display = 'none';
    document.getElementById('divBtnStep2').style.display = 'flex';
    document.getElementById('divNotaSeguranca').style.display = 'none';
    atualizarStepper(2);
});

// ── Confirmar e processar pagamento ──
var aguardandoConfirmacao = false;

document.getElementById('btnConfirmarPagamento').addEventListener('click', function () {
    if (aguardandoConfirmacao) return;
    var metodo = document.getElementById('inputMetodo').value;
    if (!metodo) return;

    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Criando assinatura...';

    document.getElementById('divResumoConfirmar').style.display = 'none';

    if (metodo === 'pix') {
        document.getElementById('pixLoading').style.display = 'block';
    }

    var formData = new FormData(document.getElementById('formContratar'));

    fetch('{{ route('assinatura.contratar.confirmar') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: formData,
    })
    .then(function (r) {
        var ct = r.headers.get('Content-Type') || '';
        if (ct.includes('application/json')) {
            return r.json().then(function (d) { return { status: r.status, data: d, isJson: true }; });
        }
        return r.text().then(function () { return { status: r.status, isJson: false, url: r.url }; });
    })
    .then(function (res) {
        if (!res.isJson) {
            window.location.href = res.url || '{{ route('assinatura.meu-plano') }}';
            return;
        }

        if (!res.data.success) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-flag-checkered mr-1"></i> Confirmar Contratacao';
            document.getElementById('pixLoading').style.display = 'none';
            document.getElementById('divResumoConfirmar').style.display = 'block';
            alert(res.data.message || res.data.error || 'Erro ao criar assinatura.');
            return;
        }

        PAGAMENTO_ID = res.data.pagamento_id;
        atualizarStepper(2);

        if (metodo === 'pix') {
            document.getElementById('divBtnStep2').style.display = 'none';
            document.getElementById('divPagamentoPix').style.display = 'block';
            gerarPix(res.data.pagamento_id);
        } else if (metodo === 'cartao') {
            document.getElementById('divPagamentoCartao').style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-flag-checkered mr-1"></i> Confirmar Contratacao';
            aguardandoConfirmacao = true;
            btn.onclick = function () { processarCartao(PAGAMENTO_ID); };
        }
    })
    .catch(function () {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-flag-checkered mr-1"></i> Confirmar Contratacao';
        document.getElementById('pixLoading').style.display = 'none';
        document.getElementById('divResumoConfirmar').style.display = 'block';
        alert('Erro de conexao. Tente novamente.');
    });
});

// ── PIX ──
var pixPollingTimer = null;

function gerarPix(pagamentoId) {
    document.getElementById('pixLoading').style.display = 'block';
    document.getElementById('pixConteudo').style.display = 'none';
    document.getElementById('pixErro').style.display = 'none';

    fetch('/meu-plano/pagamento/' + pagamentoId + '/pix', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        document.getElementById('pixLoading').style.display = 'none';
        if (data.error) {
            document.getElementById('pixErro').textContent = data.error;
            document.getElementById('pixErro').style.display = 'block';
            return;
        }
        document.getElementById('pixQrImage').src = data.qr_image || '';
        document.getElementById('pixCopiaCola').value = data.qr_text || '';
        document.getElementById('pixValorExibido').textContent = 'R$ ' + (data.valor || '');
        document.getElementById('pixConteudo').style.display = 'block';
        if (data.expira_em) {
            document.getElementById('pixExpira').textContent = 'Expira em: ' + new Date(data.expira_em).toLocaleTimeString('pt-BR');
        }
        iniciarPollingPix(pagamentoId);
    })
    .catch(function () {
        document.getElementById('pixLoading').style.display = 'none';
        document.getElementById('pixErro').textContent = 'Erro ao gerar PIX. Tente novamente.';
        document.getElementById('pixErro').style.display = 'block';
    });
}

function iniciarPollingPix(pagamentoId) {
    if (pixPollingTimer) clearInterval(pixPollingTimer);
    pixPollingTimer = setInterval(function () {
        fetch('/meu-plano/pagamento/' + pagamentoId + '/status', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.pago) {
                clearInterval(pixPollingTimer);
                document.getElementById('pixAguardando').style.display = 'none';
                document.getElementById('pixPago').style.display = 'block';
                atualizarStepper(3);
                setTimeout(function () {
                    window.location.href = '{{ route('assinatura.concluido') }}?pagamento=' + pagamentoId;
                }, 2000);
            }
        })
        .catch(function () {});
    }, 4000);
}

function copiarPixCode() {
    var el = document.getElementById('pixCopiaCola');
    if (!el) return;
    navigator.clipboard.writeText(el.value).then(function () {
        var ic = document.getElementById('pixCopyIcon');
        if (ic) { ic.className = 'fas fa-check text-success'; setTimeout(function () { ic.className = 'fas fa-copy'; }, 2000); }
    });
}

// ── Cartao ──
function inicializarCartao(pagamentoId) {
    fetch('/meu-plano/pagamento/' + pagamentoId + '/cartao-intent?peek=1', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ peek: true }),
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.stripe_key) {
            stripe = Stripe(data.stripe_key);
            var elements = stripe.elements({ locale: 'pt-BR' });
            var card = elements.create('card', {
                style: {
                    base: { fontSize: '15px', color: '#0f172a', fontFamily: 'Inter, sans-serif', '::placeholder': { color: '#94a3b8' } },
                }
            });
            card.mount('#card-element');
            card.on('change', function (event) {
                var erroEl = document.getElementById('card-errors');
                if (event.error) {
                    erroEl.textContent = event.error.message;
                    erroEl.style.display = 'block';
                } else {
                    erroEl.textContent = '';
                    erroEl.style.display = 'none';
                }
            });
        }
    });
}

async function processarCartao(pagamentoId) {
    var btn = document.getElementById('btnConfirmarPagamento');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Processando...';

    document.getElementById('cartaoProcessando').style.display = 'block';
    document.getElementById('cartaoSucesso').style.display = 'none';
    document.getElementById('cartaoErro').style.display = 'none';

    try {
        var r1 = await fetch('/meu-plano/pagamento/' + pagamentoId + '/cartao-intent', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({
                salvar_cartao: document.getElementById('salvarCartao')?.checked || false,
            }),
        });
        var data1 = await r1.json();
        if (data1.error) throw new Error(data1.error);

        cartaoClientSecret = data1.client_secret;

        var result = await stripe.confirmCardPayment(cartaoClientSecret);
        if (result.error) throw new Error(result.error.message);

        if (result.paymentIntent.status !== 'succeeded') {
            throw new Error('Pagamento nao aprovado. Tente novamente.');
        }

        var r2 = await fetch('/meu-plano/pagamento/' + pagamentoId + '/cartao-confirmar', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ payment_intent_id: result.paymentIntent.id }),
        });
        var data2 = await r2.json();
        if (data2.error) throw new Error(data2.error);

        document.getElementById('cartaoProcessando').style.display = 'none';
        document.getElementById('cartaoSucesso').style.display = 'block';
        atualizarStepper(3);

        setTimeout(function () {
            window.location.href = data2.redirect || '{{ route('assinatura.concluido') }}?pagamento=' + pagamentoId;
        }, 2000);

    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-flag-checkered mr-1"></i> Confirmar Contratacao';
        document.getElementById('cartaoProcessando').style.display = 'none';
        document.getElementById('cartaoErro').textContent = err.message || 'Erro ao processar pagamento.';
        document.getElementById('cartaoErro').style.display = 'block';
    }
}

// Restaura selecao ao carregar
var metodoSalvo = document.getElementById('inputMetodo').value;
if (metodoSalvo) {
    var card = document.querySelector('.metodo-card[data-metodo="' + metodoSalvo + '"]');
    if (card) selecionarMetodo(metodoSalvo, card);
}
</script>
@endpush
