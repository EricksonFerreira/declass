@php
    $etapaAtiva = 'onboarding.pagamento';
    $etapasConc = ['onboarding.empresa', 'onboarding.plano'];
@endphp

@extends('onboarding._layout')
@section('page_title', 'Forma de Pagamento')

@push('styles')
<style>
.metodo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
@media (max-width: 560px) { .metodo-grid { grid-template-columns: 1fr; } }
.metodo-card {
    border: 2px solid #e2e8f0; border-radius: 14px; padding: 1.25rem;
    cursor: pointer; transition: border-color .2s, box-shadow .2s;
    text-align: center; background: #fff;
}
.metodo-card:hover { border-color: #0a1628; }
.metodo-card.selected { border-color: #0a1628; box-shadow: 0 0 0 3px rgba(10,22,40,.07); }
.metodo-icon { font-size: 1.8rem; margin-bottom: .6rem; color: #0a1628; }
.metodo-nome { font-weight: 700; font-size: .9rem; color: #0a1628; margin-bottom: .3rem; }
.metodo-desc { font-size: .75rem; color: #64748b; line-height: 1.5; }
.periodo-resumo {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
    padding: 1.25rem; margin-bottom: 1.5rem;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap:.75rem;
}
.periodo-resumo-valor { font-family: 'Raleway', sans-serif; font-weight: 900; font-size: 1.8rem; color: #0a1628; }
.info-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 1rem; font-size: .83rem; color: #1d4ed8; line-height: 1.6; }
</style>
@endpush

@section('content')
<div class="onb-card">
    <h1 class="onb-card-title">Como você prefere pagar?</h1>
    <p class="onb-card-sub">Escolha a forma de cobrança que melhor se encaixa na sua rotina financeira.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-circle mr-1"></i></strong>
            <ul style="margin:.4rem 0 0 1.2rem;">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Resumo do plano escolhido --}}
    <div class="periodo-resumo">
        <div>
            <div style="font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:.25rem;">Plano selecionado</div>
            <div style="font-weight:700; color:#0a1628; font-size:1.05rem;">{{ $plano->nome }}</div>
            <div style="font-size:.8rem; color:#64748b;">
                {{ $periodicidade === 'anual' ? 'Cobrança anual' : 'Cobrança mensal' }}
                @if ($periodicidade === 'anual' && $plano->desconto_anual_percentual > 0)
                    <span style="background:#dcfce7;color:#15803d;font-size:.7rem;font-weight:700;padding:.1rem .4rem;border-radius:999px;margin-left:.3rem;">
                        {{ $plano->desconto_anual_percentual }}% off
                    </span>
                @endif
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:.72rem; color:#94a3b8; font-weight:600;">por mês</div>
            <div class="periodo-resumo-valor">
                R$ {{ number_format($periodicidade === 'anual' ? $plano->valor_anual : $plano->valor_mensal, 2, ',', '.') }}
            </div>
            @if ($periodicidade === 'anual' && $plano->economia_anual > 0)
                <div style="font-size:.75rem; color:#16a34a; font-weight:600;">
                    Economia de R$ {{ number_format($plano->economia_anual, 2, ',', '.') }}/ano
                </div>
            @endif
            <a href="{{ route('onboarding.plano') }}" style="font-size:.75rem; color:#64748b; text-decoration:underline;">Trocar plano</a>
        </div>
    </div>

    <form action="{{ route('onboarding.plano.confirmar') }}" method="POST" id="formPagamento">
        @csrf
        <input type="hidden" name="plano_id" value="{{ $plano->id }}">
        <input type="hidden" name="periodicidade" value="{{ $periodicidade }}">
        <input type="hidden" name="metodo_pagamento" id="inputMetodo" value="">

        <div style="margin-bottom:1rem;">
            <label class="form-label">Método de pagamento <span style="color:#ef4444;">*</span></label>
        </div>

        <div class="metodo-grid">
            <div class="metodo-card" onclick="selecionarMetodo('boleto', this)">
                <div class="metodo-icon"><i class="fas fa-barcode"></i></div>
                <div class="metodo-nome">Boleto Bancário</div>
                <div class="metodo-desc">Boleto enviado por e-mail. Compensação em até 3 dias úteis.</div>
            </div>
            <div class="metodo-card" onclick="selecionarMetodo('pix', this)">
                <div class="metodo-icon"><i class="fas fa-qrcode"></i></div>
                <div class="metodo-nome">PIX</div>
                <div class="metodo-desc">Pagamento instantâneo via chave PIX. Ativação mais rápida.</div>
            </div>
            <div class="metodo-card" onclick="selecionarMetodo('automatico', this)">
                <div class="metodo-icon"><i class="fas fa-sync-alt"></i></div>
                <div class="metodo-nome">Débito Automático</div>
                <div class="metodo-desc">Cobrança recorrente automática na data escolhida.</div>
            </div>
        </div>

        <div id="errMetodo" class="alert alert-danger" style="display:none;">
            <i class="fas fa-exclamation-circle mr-1"></i> Escolha um método de pagamento.
        </div>

        {{-- Campo dia de vencimento (só para automático) --}}
        <div id="divDiaVenc" style="display:none; margin-bottom:1.25rem;">
            <label class="form-label">Dia do mês para cobrança automática</label>
            <div style="max-width:180px;">
                <input type="number" name="dia_vencimento" class="form-control" min="1" max="28" placeholder="Ex.: 10">
            </div>
            <div style="font-size:.75rem; color:#64748b; margin-top:.3rem;">
                Escolha um dia entre 1 e 28 para garantir cobrança em todos os meses.
            </div>
        </div>

        <div class="info-box" style="margin-bottom:1.5rem;">
            <i class="fas fa-shield-alt mr-1"></i>
            <strong>Sem cobrança imediata.</strong> Após confirmar, você receberá as instruções de pagamento por e-mail.
            A sua conta ficará ativa em modo de avaliação até o pagamento ser confirmado.
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.75rem;">
            <a href="{{ route('onboarding.plano') }}" style="font-size:.875rem; color:#64748b;">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
            <button type="button" onclick="confirmar()" class="btn btn-primary btn-lg">
                <i class="fas fa-check mr-1"></i> Confirmar e Continuar
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function selecionarMetodo(metodo, el) {
    document.querySelectorAll('.metodo-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('inputMetodo').value = metodo;
    document.getElementById('errMetodo').style.display = 'none';
    document.getElementById('divDiaVenc').style.display = metodo === 'automatico' ? 'block' : 'none';
}

function confirmar() {
    if (!document.getElementById('inputMetodo').value) {
        document.getElementById('errMetodo').style.display = 'block';
        return;
    }
    document.getElementById('formPagamento').submit();
}
</script>
@endpush
