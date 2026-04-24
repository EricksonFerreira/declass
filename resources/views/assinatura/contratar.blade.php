@extends('adminlte::page')

@section('title', 'Contratar — ' . $plano->nome)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
        <div>
            <h1 class="m-0 text-dark" style="font-size:1.3rem;font-weight:700;">
                Contratar: <span style="color:#0a1628;">{{ $plano->nome }}</span>
            </h1>
            <small class="text-muted">Confirme as condições e escolha a forma de pagamento</small>
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

    <form action="{{ route('assinatura.contratar.confirmar') }}" method="POST" id="formContratar">
        @csrf
        <input type="hidden" name="plano_id" value="{{ $plano->id }}">
        <input type="hidden" name="periodicidade" id="inputPeriodicidade" value="{{ $periodicidade }}">
        <input type="hidden" name="metodo_pagamento" id="inputMetodo" value="{{ old('metodo_pagamento') }}">
        <input type="hidden" name="renovacao_automatica" id="inputRenovacaoAutomatica" value="0">

        <div class="row">

            {{-- ── COLUNA ESQUERDA: resumo do plano ───────────────────────── --}}
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

                        {{-- Toggle mensal / anual --}}
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
                            <div class="text-muted mb-1" style="font-size:.875rem;">/mês</div>
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

            {{-- ── COLUNA DIREITA: forma de pagamento ─────────────────────── --}}
            <div class="col-lg-7 mb-3">

                {{-- Passo 1: escolher método --}}
                <div class="card mb-3" style="border-radius:12px;overflow:hidden;">
                    <div class="card-header py-2" style="background:#f8fafc;">
                        <h5 class="mb-0" style="font-size:.9rem;font-weight:700;">
                            <span class="badge badge-primary mr-2">1</span> Forma de Pagamento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="metodosGrid">
                            @foreach ([
                                ['pix',    'fas fa-qrcode',      'PIX',               'Pagamento instantâneo via QR Code. Ativação imediata após confirmação.'],
                                ['cartao', 'fas fa-credit-card', 'Cartão de Crédito', 'Pague com cartão de crédito. Possibilidade de renovação automática.'],
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

                {{-- Painel cartão --}}
                <div id="divCartao" class="card mb-3" style="display:none;border:1.5px solid #bae6fd;border-radius:12px;overflow:hidden;">
                    <div class="card-header py-2" style="background:#f0f9ff;">
                        <h5 class="mb-0" style="font-size:.88rem;font-weight:700;color:#0369a1;">
                            <i class="fas fa-credit-card mr-1"></i> Opções do Cartão de Crédito
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start" style="gap:1rem;">
                            <div class="flex-grow-1">
                                <div class="font-weight-bold mb-1" style="font-size:.88rem;">
                                    <i class="fas fa-sync-alt mr-1 text-primary"></i> Renovação Automática
                                </div>
                                <p class="text-muted mb-0" style="font-size:.8rem;">
                                    Ative para nunca perder o acesso. A assinatura é renovada automaticamente a cada período. Você pode cancelar a qualquer momento.
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
                            <strong>Renovação automática ativa.</strong> O pagamento será processado automaticamente via Stripe.
                        </div>
                        <div id="divRenovacaoInativa" class="mt-2 alert alert-info py-2 mb-0" style="font-size:.8rem;">
                            <i class="fas fa-info-circle mr-1"></i>
                            Pagamento avulso. Após confirmar você será direcionado para inserir os dados do cartão.
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
                            <a href="https://wa.me/558192554172?text={{ urlencode('Olá! Quero contratar o plano ' . $plano->nome . '. Podem me ajudar?') }}"
                               target="_blank" rel="noopener" class="btn btn-success btn-sm">
                                <i class="fab fa-whatsapp mr-1"></i> Chamar no WhatsApp
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Nota de segurança (oculta quando atendente selecionado) --}}
                <div id="divNotaSeguranca" class="alert alert-light border mb-3" style="font-size:.82rem;">
                    <i class="fas fa-shield-alt text-primary mr-1"></i>
                    <strong>Sem cobrança imediata.</strong>
                    Após confirmar, você insere os dados de pagamento. Sua conta fica ativa em modo de avaliação até a confirmação.
                </div>

                {{-- Botão confirmar (oculto quando atendente selecionado) --}}
                <div id="divBtnConfirmar" class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('assinatura.meu-plano') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Cancelar
                    </a>
                    <button type="button" onclick="confirmar()" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-check mr-1"></i> Confirmar Contratação
                    </button>
                </div>
            </div>

        </div>
    </form>
@stop

@push('js')
<script>
const valorMensal = '{{ number_format($plano->valor_mensal, 2, ",", ".") }}';
const valorAnual  = '{{ number_format($plano->valor_anual, 2, ",", ".") }}';

document.getElementById('togglePeriodo').addEventListener('change', function () {
    const anual = this.checked;
    document.getElementById('inputPeriodicidade').value = anual ? 'anual' : 'mensal';
    document.getElementById('valorExibido').textContent  = anual ? valorAnual : valorMensal;
    document.getElementById('notaAnual').style.display   = anual ? 'block' : 'none';
    document.getElementById('lblMensal').style.fontWeight = anual ? '400' : '700';
    document.getElementById('lblMensal').style.color      = anual ? '#94a3b8' : '#0a1628';
    document.getElementById('lblAnual').style.fontWeight  = anual ? '700' : '400';
    document.getElementById('lblAnual').style.color       = anual ? '#0a1628' : '#94a3b8';
});

function selecionarMetodo(metodo, el) {
    document.querySelectorAll('.metodo-card').forEach(c => {
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
    document.getElementById('divBtnConfirmar').style.display  = metodo === 'atendente' ? 'none' : 'flex';

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

// Restaura seleção ao carregar (old())
const metodoSalvo = document.getElementById('inputMetodo').value;
if (metodoSalvo) {
    const card = document.querySelector(`.metodo-card[data-metodo="${metodoSalvo}"]`);
    if (card) selecionarMetodo(metodoSalvo, card);
}

function confirmar() {
    if (!document.getElementById('inputMetodo').value) {
        document.getElementById('errMetodo').style.display = 'block';
        document.getElementById('errMetodo').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    document.getElementById('formContratar').submit();
}
</script>
@endpush
