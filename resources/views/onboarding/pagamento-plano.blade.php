@php
    $etapaAtiva = 'onboarding.pagamento';
    $etapasConc = ['onboarding.empresa', 'onboarding.plano'];
    $plano      = $pagamento->empresaPlano?->plano;
    $metodo     = $pagamento->metodo ?? 'cartao';
@endphp

@extends('onboarding._layout')
@section('page_title', 'Pagamento')

@push('styles')
<style>
.metodo-btn {
    display: flex; align-items: center; gap: 1rem;
    padding: 1.1rem 1.4rem; border-radius: 14px;
    border: 2px solid #e2e8f0; background: #fff;
    cursor: pointer; transition: border-color .15s, box-shadow .15s, background .15s;
    width: 100%; text-align: left; font-family: inherit;
}
.metodo-btn:hover { border-color: #0a1628; }
.metodo-btn.ativo  { border-color: #0a1628; background: #f8faff; box-shadow: 0 0 0 3px rgba(10,22,40,.08); }
.metodo-icon { width: 44px; height: 44px; border-radius: 12px; background: #f1f5f9;
    display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #0a1628; flex-shrink: 0; }
.metodo-btn.ativo .metodo-icon { background: #0a1628; color: #fff; }
.metodo-label { font-weight: 700; font-size: .9rem; color: #0f172a; }
.metodo-desc  { font-size: .78rem; color: #64748b; margin-top: .1rem; }

.dia-btn {
    padding: .5rem .9rem; border-radius: 8px; border: 1.5px solid #e2e8f0;
    background: #f8fafc; font-size: .85rem; font-weight: 600; color: #374151;
    cursor: pointer; transition: all .15s; font-family: inherit;
}
.dia-btn:hover { border-color: #0a1628; background: #f1f5f9; }
.dia-btn.ativo  { border-color: #0a1628; background: #0a1628; color: #fff; }

.resumo-box {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
    padding: 1rem 1.2rem; margin-bottom: 1.5rem;
    display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .5rem;
}
.resumo-plano { font-weight: 700; font-size: .95rem; color: #0f172a; }
.resumo-valor { font-size: 1.4rem; font-weight: 900; color: #0a1628; }
.resumo-sub   { font-size: .78rem; color: #64748b; }

/* Stepper mini */
.pag-step { display: flex; align-items: center; gap: .5rem; margin-bottom: 1.8rem; flex-wrap: wrap; }
.pag-step-item { display: flex; align-items: center; gap: .4rem; font-size: .78rem; font-weight: 600; color: #94a3b8; }
.pag-step-item.ativo { color: #0a1628; }
.pag-step-item.feito { color: #22c55e; }
.pag-step-num { width: 22px; height: 22px; border-radius: 50%; border: 2px solid currentColor;
    display: flex; align-items: center; justify-content: center; font-size: .68rem; flex-shrink: 0; }
.pag-step-item.feito  .pag-step-num { background: #22c55e; border-color: #22c55e; color: #fff; }
.pag-step-item.ativo  .pag-step-num { background: #0a1628; border-color: #0a1628; color: #fff; }
.pag-step-sep { color: #cbd5e1; font-size: .65rem; }

/* Seção */
.sec-title { font-weight: 700; font-size: .85rem; color: #374151; margin-bottom: .75rem;
    display: flex; align-items: center; gap: .4rem; }
.sec-num { width: 20px; height: 20px; border-radius: 50%; background: #0a1628; color: #fff;
    font-size: .65rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }

/* Stripe card element */
#card-element { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: .75rem; background: #f8fafc; }
#card-element.StripeElement--focus { border-color: #0a1628; box-shadow: 0 0 0 3px rgba(10,22,40,.08); }
#card-errors { color: #ef4444; font-size: .8rem; margin-top: .4rem; }

.security-note { font-size: .77rem; color: #94a3b8; display: flex; align-items: center; gap: .3rem; margin-top: .75rem; }

/* Steps orientação */
.orient-steps { display: flex; flex-direction: column; gap: .5rem; margin-top: 1.5rem; }
.orient-step { display: flex; align-items: center; gap: .75rem; padding: .7rem .9rem; border-radius: 10px;
    border: 1px solid #e2e8f0; background: #fff; font-size: .82rem; }
.orient-step.feito { border-color: #22c55e; background: #f0fdf4; }
.orient-step.atual { border-color: #0a1628; background: #f8faff; font-weight: 700; }
.orient-step.bloq  { opacity: .45; }
.orient-step-icon { width: 30px; height: 30px; border-radius: 8px; background: #f1f5f9; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: .8rem; color: #0a1628; }
.orient-step.feito .orient-step-icon { background: #dcfce7; color: #16a34a; }
.orient-step.atual .orient-step-icon { background: #0a1628; color: #fff; }
</style>
@endpush

@section('content')
<div style="display:grid; grid-template-columns: 1fr 340px; gap: 1.5rem; max-width: 1000px; width:100%; align-items:start;">

    {{-- ── COLUNA PRINCIPAL: pagamento ── --}}
    <div class="onb-card" style="max-width:100%;">

        <h1 class="onb-card-title">Realizar o pagamento</h1>
        <p class="onb-card-sub">Escolha a forma de pagamento e conclua para liberar todos os recursos.</p>

        {{-- Resumo do plano --}}
        <div class="resumo-box">
            <div>
                <div class="resumo-plano">
                    <i class="fas fa-tags" style="font-size:.8rem; margin-right:.3rem;"></i>
                    {{ $plano?->nome ?? 'Plano' }}
                    <span class="resumo-sub ml-2">· {{ ucfirst($pagamento->periodicidade ?? 'mensal') }}</span>
                </div>
                <div class="resumo-sub">Ref. {{ $pagamento->referencia_interna }} · Vencimento: {{ $pagamento->data_vencimento->format('d/m/Y') }}</div>
            </div>
            <div>
                <div class="resumo-valor">R$ {{ number_format($pagamento->valor, 2, ',', '.') }}</div>
                <div class="resumo-sub" style="text-align:right;">/mês</div>
            </div>
        </div>

        @if (session('error'))
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        {{-- Stepper mini --}}
        <div class="pag-step">
            <div class="pag-step-item feito" id="pstep1">
                <div class="pag-step-num"><i class="fas fa-check" style="font-size:.55rem;"></i></div> Método
            </div>
            <span class="pag-step-sep"><i class="fas fa-chevron-right"></i></span>
            <div class="pag-step-item" id="pstep2">
                <div class="pag-step-num">2</div> Detalhes
            </div>
            <span class="pag-step-sep"><i class="fas fa-chevron-right"></i></span>
            <div class="pag-step-item" id="pstep3">
                <div class="pag-step-num">3</div> Confirmação
            </div>
        </div>

        {{-- Passo 1: escolher método --}}
        <div id="secMetodo">
            <div class="sec-title"><span class="sec-num">1</span> Forma de pagamento</div>
            <div style="display:flex; flex-direction:column; gap:.75rem; margin-bottom:1.5rem;">
                <button type="button" class="metodo-btn ativo" id="btnCartao" onclick="escolherMetodo('cartao')">
                    <div class="metodo-icon"><i class="fas fa-credit-card"></i></div>
                    <div>
                        <div class="metodo-label">Cartão de Crédito</div>
                        <div class="metodo-desc">Pague agora com cartão. Possibilidade de renovação automática.</div>
                    </div>
                </button>
            </div>
            <div id="errMetodo" class="alert alert-danger" style="display:none;">
                <i class="fas fa-exclamation-circle"></i> Escolha uma forma de pagamento.
            </div>
            <div style="display:flex; justify-content:flex-end;">
                <button type="button" class="btn btn-primary" onclick="avancarParaDetalhes()">
                    Continuar <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- Passo 2 cartão: dia de vencimento --}}
        <div id="secDiaVencimento" style="display:none;">
            <div class="sec-title"><span class="sec-num">2</span> Escolha o dia de vencimento</div>
            <p style="font-size:.83rem; color:#64748b; margin-bottom:1rem;">Escolha o dia do mês em que prefere ser cobrado mensalmente.</p>
            <div style="display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:1.5rem;" id="diasGrid">
                @foreach ([5, 10, 15, 20, 25, 28] as $dia)
                    <button type="button" class="dia-btn {{ $dia === 10 ? 'ativo' : '' }}"
                            data-dia="{{ $dia }}"
                            onclick="selecionarDia({{ $dia }}, this)">
                        Dia {{ $dia }}
                    </button>
                @endforeach
            </div>
            <input type="hidden" id="diaVencimentoSelecionado" value="10">
            <div style="display:flex; justify-content:space-between;">
                <button type="button" class="btn btn-secondary" onclick="voltarParaMetodo()">
                    <i class="fas fa-arrow-left"></i> Voltar
                </button>
                <button type="button" class="btn btn-primary" onclick="avancarParaPagamento()">
                    Continuar <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- Passo 3: dados do cartão --}}
        <div id="secCartao" style="display:none;">
            <div class="sec-title"><span class="sec-num">3</span> Dados do cartão</div>
            <div style="margin-bottom:1rem;">
                <label class="form-label">Número do cartão, validade e CVC</label>
                <div id="card-element"></div>
                <div id="card-errors"></div>
            </div>
            <label style="display:flex; align-items:center; gap:.5rem; font-size:.83rem; cursor:pointer; margin-bottom:1rem;">
                <input type="checkbox" id="chkSalvar"> Salvar cartão para renovações futuras
            </label>
            <div class="security-note">
                <i class="fas fa-shield-alt" style="color:#0a1628;"></i>
                Pagamento seguro via Stripe. Seus dados não são armazenados em nossos servidores.
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:1.25rem;">
                <button type="button" class="btn btn-secondary" onclick="voltarParaDia()">
                    <i class="fas fa-arrow-left"></i> Voltar
                </button>
                <button type="button" class="btn btn-primary" id="btnPagar" onclick="processarCartao()">
                    <i class="fas fa-lock"></i> Pagar R$ {{ number_format($pagamento->valor, 2, ',', '.') }}
                </button>
            </div>
            <div id="erroCartao" class="alert alert-danger mt-3" style="display:none;"></div>
        </div>

        {{-- Confirmação de pagamento --}}
        <div id="secSucesso" style="display:none; text-align:center; padding:1.5rem 0;">
            <div style="font-size:3rem; margin-bottom:.75rem;">🎉</div>
            <div style="font-size:1.2rem; font-weight:900; color:#15803d; margin-bottom:.5rem;">Pagamento confirmado!</div>
            <div style="font-size:.85rem; color:#64748b; margin-bottom:1.5rem;">Seu plano foi ativado. Vamos configurar o resto da sua empresa.</div>
            <a href="{{ route('onboarding.orientacoes') }}" class="btn btn-primary btn-lg">
                Continuar configuração <i class="fas fa-arrow-right"></i>
            </a>
        </div>

    </div>

    {{-- ── COLUNA LATERAL: orientações ── --}}
    <div>
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:1.4rem;">
            <div style="font-weight:700; font-size:.88rem; color:#374151; margin-bottom:1rem;">
                <i class="fas fa-route" style="margin-right:.4rem; color:#0a1628;"></i> Sua jornada de configuração
            </div>
            <div class="orient-steps">
                @php
                    $stepsOrient = [
                        ['label' => 'Dados da empresa',     'icone' => 'building',        'feito' => true,  'atual' => false],
                        ['label' => 'Escolher plano',       'icone' => 'tags',             'feito' => true,  'atual' => false],
                        ['label' => 'Realizar pagamento',   'icone' => 'credit-card',      'feito' => false, 'atual' => true ],
                        ['label' => 'Criar projetos',       'icone' => 'folder-open',      'feito' => false, 'atual' => false],
                        ['label' => 'Montar equipe',        'icone' => 'users',            'feito' => false, 'atual' => false],
                    ];
                @endphp
                @foreach ($stepsOrient as $s)
                    @php
                        $cls = $s['feito'] ? 'feito' : ($s['atual'] ? 'atual' : 'bloq');
                    @endphp
                    <div class="orient-step {{ $cls }}">
                        <div class="orient-step-icon">
                            @if ($s['feito'])
                                <i class="fas fa-check"></i>
                            @elseif ($s['atual'])
                                <i class="fas fa-{{ $s['icone'] }}"></i>
                            @else
                                <i class="fas fa-lock" style="font-size:.7rem; color:#cbd5e1;"></i>
                            @endif
                        </div>
                        <div style="font-size:.82rem; color:{{ $s['feito'] ? '#15803d' : ($s['atual'] ? '#0f172a' : '#94a3b8') }};">
                            {{ $s['label'] }}
                        </div>
                        @if ($s['feito'])
                            <i class="fas fa-check-circle" style="margin-left:auto; color:#22c55e; font-size:.85rem;"></i>
                        @elseif ($s['atual'])
                            <i class="fas fa-arrow-right" style="margin-left:auto; color:#0a1628; font-size:.75rem;"></i>
                        @endif
                    </div>
                @endforeach
            </div>
            <div style="margin-top:1.2rem; font-size:.75rem; color:#94a3b8; line-height:1.5;">
                <i class="fas fa-shield-alt" style="margin-right:.3rem;"></i>
                Após o pagamento todos os recursos do plano <strong>{{ $plano?->nome }}</strong> são liberados imediatamente.
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const pagamentoId  = {{ $pagamento->id }};
const urlIntent    = "{{ route('assinatura.pagamento.cartao-intent', $pagamento) }}";
const urlConfirmar = "{{ route('assinatura.pagamento.cartao-confirmar', $pagamento) }}";
const urlOrient    = "{{ route('onboarding.orientacoes') }}";
const csrfToken    = "{{ csrf_token() }}";

let metodoAtual   = 'cartao';
let stripeInst    = null;
let cardElement   = null;

// ── Navegação entre steps ───────────────────────────────────────────────────

function escolherMetodo(m) {
    metodoAtual = m;
    document.getElementById('btnCartao').classList.toggle('ativo', m === 'cartao');
    document.getElementById('errMetodo').style.display = 'none';
    atualizarPagStep(1);
}

function avancarParaDetalhes() {
    if (!metodoAtual) {
        document.getElementById('errMetodo').style.display = 'block';
        return;
    }
    esconder('secMetodo');
    mostrar('secDiaVencimento');
    atualizarPagStep(2);
}

function avancarParaPagamento() {
    esconder('secDiaVencimento');
    mostrar('secCartao');
    atualizarPagStep(3);
    inicializarStripe();
}

function voltarParaMetodo() {
    esconder('secCartao');
    esconder('secDiaVencimento');
    mostrar('secMetodo');
    atualizarPagStep(1);
}

function voltarParaDia() {
    esconder('secCartao');
    mostrar('secDiaVencimento');
    atualizarPagStep(2);
}

function selecionarDia(dia, el) {
    document.querySelectorAll('.dia-btn').forEach(b => b.classList.remove('ativo'));
    el.classList.add('ativo');
    document.getElementById('diaVencimentoSelecionado').value = dia;
}

function atualizarPagStep(ativo) {
    [1,2,3].forEach(n => {
        const el = document.getElementById('pstep' + n);
        el.classList.remove('ativo', 'feito');
        if (n < ativo) el.classList.add('feito');
        else if (n === ativo) el.classList.add('ativo');
    });
}

function mostrar(id) { document.getElementById(id).style.display = 'block'; }
function esconder(id) { document.getElementById(id).style.display = 'none'; }

// ── Cartão (Stripe Elements) ─────────────────────────────────────────────────

async function inicializarStripe() {
    if (stripeInst) return;
    try {
        const res  = await fetch(urlIntent + '?peek=1', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        const data = await res.json();
        if (!data.stripe_key) throw new Error('Não foi possível carregar o Stripe.');

        stripeInst  = Stripe(data.stripe_key);
        const elements = stripeInst.elements();
        cardElement = elements.create('card', {
            style: {
                base:    { fontSize: '16px', color: '#0f172a', fontFamily: 'Inter, sans-serif' },
                invalid: { color: '#ef4444' },
            },
        });
        cardElement.mount('#card-element');
        cardElement.on('change', e => {
            document.getElementById('card-errors').textContent = e.error ? e.error.message : '';
        });
    } catch (e) {
        document.getElementById('card-errors').textContent = e.message;
    }
}

async function processarCartao() {
    const btn = document.getElementById('btnPagar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando…';
    document.getElementById('erroCartao').style.display = 'none';

    try {
        const salvar = document.getElementById('chkSalvar').checked;
        const res    = await fetch(urlIntent, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ salvar_cartao: salvar }),
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);

        const { error, paymentIntent } = await stripeInst.confirmCardPayment(data.client_secret, {
            payment_method: { card: cardElement },
        });

        if (error) throw new Error(error.message);
        if (paymentIntent.status !== 'succeeded') throw new Error('Pagamento não confirmado. Tente novamente.');

        // Registra confirmação no backend
        const confRes  = await fetch(urlConfirmar, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ payment_intent_id: paymentIntent.id }),
        });
        const confData = await confRes.json();
        if (confData.error) throw new Error(confData.error);

        mostrarSucesso();
    } catch (e) {
        const el = document.getElementById('erroCartao');
        el.textContent = e.message;
        el.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> Pagar R$ {{ number_format($pagamento->valor, 2, ",", ".") }}';
    }
}

function mostrarSucesso() {
    ['secMetodo','secDiaVencimento','secCartao'].forEach(esconder);
    mostrar('secSucesso');
    [1,2,3].forEach(n => {
        const el = document.getElementById('pstep' + n);
        el.classList.remove('ativo');
        el.classList.add('feito');
    });
}

// Inicializa Stripe.js assincronamente
(function loadStripe() {
    if (typeof Stripe !== 'undefined') return;
    const s = document.createElement('script');
    s.src = 'https://js.stripe.com/v3/';
    document.head.appendChild(s);
})();

// Inicia no método atual (se já definido pelo backend)
window.addEventListener('DOMContentLoaded', () => {
    atualizarPagStep(1);
});
</script>
@endpush
