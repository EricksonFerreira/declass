@php
    $etapaAtiva = 'onboarding.orientacoes';
    $etapasConc = ['onboarding.empresa', 'onboarding.plano', 'onboarding.pagamento'];
@endphp

@extends('onboarding._layout')
@section('page_title', 'Primeiros Passos')

@push('styles')
<style>
.steps-list { display: flex; flex-direction: column; gap: .75rem; margin-bottom: 2rem; }
.step-item {
    display: flex; align-items: flex-start; gap: 1rem;
    padding: 1.1rem 1.25rem; border-radius: 14px;
    border: 1.5px solid #e2e8f0; background: #fff;
    transition: border-color .2s, box-shadow .2s;
}
.step-item:not(.bloqueado):hover { border-color: #0a1628; box-shadow: 0 4px 16px rgba(10,22,40,.07); }
.step-item.feito { border-color: #22c55e; background: #f0fdf4; }
.step-item.bloqueado { border-color: #e2e8f0; background: #f8fafc; opacity: .55; cursor: not-allowed; }
.step-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: #f1f5f9; display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: #0a1628; flex-shrink: 0;
    transition: background .2s;
}
.step-item.feito .step-icon { background: #dcfce7; color: #16a34a; }
.step-item.bloqueado .step-icon { background: #f1f5f9; color: #94a3b8; }
.step-body { flex: 1; }
.step-label { font-weight: 700; font-size: .9rem; color: #0f172a; margin-bottom: .2rem; }
.step-desc { font-size: .8rem; color: #64748b; line-height: 1.5; }
.step-item.feito .step-label { color: #15803d; }
.step-item.bloqueado .step-label { color: #94a3b8; }
.step-item.bloqueado .step-desc { color: #94a3b8; }
.step-check { color: #22c55e; font-size: .95rem; flex-shrink: 0; align-self: center; }
.step-lock { color: #cbd5e1; font-size: .95rem; flex-shrink: 0; align-self: center; }
.step-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .78rem; font-weight: 700; color: #0a1628;
    background: #f1f5f9; border: 1px solid #e2e8f0;
    padding: .3rem .7rem; border-radius: 7px; margin-top: .5rem;
    transition: background .2s;
}
.step-btn:hover { background: #e2e8f0; }
.step-bloqueado-hint {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .75rem; color: #94a3b8; margin-top: .5rem;
}

.progresso-bar-wrap { background: #f1f5f9; border-radius: 999px; height: 8px; margin: .5rem 0 .25rem; overflow: hidden; }
.progresso-bar { height: 100%; background: #0a1628; border-radius: 999px; transition: width .5s ease; }

.payment-info-box {
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px;
    padding: 1.1rem 1.25rem; margin-bottom: 1.5rem;
}

.btn-painel-disabled {
    pointer-events: none; opacity: .45; cursor: not-allowed;
}
</style>
@endpush

@section('content')
<div class="onb-card" style="max-width:680px;">
    {{-- Cabeçalho de sucesso --}}
    <div style="text-align:center; margin-bottom:2rem;">
        <div style="font-size:3rem; margin-bottom:.5rem;">🎉</div>
        <h1 class="onb-card-title" style="text-align:center;">Plano contratado com sucesso!</h1>
        <p style="font-size:.9rem; color:#64748b; line-height:1.65;">
            Sua conta está ativa em modo de avaliação. Siga os passos abaixo para
            configurar tudo e começar a usar o DeCars completo.
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
    @endif

    {{-- Info de pagamento pendente --}}
    @php
        $pagPendente = $empresa->pagamentosPlano()
            ->where('status', 'pendente')
            ->orderBy('created_at', 'desc')
            ->first();
    @endphp
    @if ($pagPendente)
        <div class="payment-info-box">
            <div style="font-weight:700; color:#92400e; margin-bottom:.4rem;">
                <i class="fas fa-clock mr-1"></i> Pagamento pendente
            </div>
            <div style="font-size:.84rem; color:#78350f; line-height:1.6;">
                {{ $pagPendente->instrucoes_pagamento ?? 'Você receberá as instruções de pagamento por e-mail em breve.' }}
            </div>
            <div style="font-size:.75rem; color:#92400e; margin-top:.5rem; font-weight:600;">
                Referência: {{ $pagPendente->referencia_interna }} · Vencimento: {{ $pagPendente->data_vencimento->format('d/m/Y') }}
            </div>
        </div>
    @endif

    {{-- Progresso --}}
    @php
        $planoAtivo = $empresa->planoAtivo()?->with('plano')->first()?->plano ?? null;
        $pct = $step->percentualConcluido($planoAtivo);
    @endphp
    <div style="margin-bottom:1.5rem;">
        <div style="display:flex; justify-content:space-between; font-size:.8rem; font-weight:600; color:#374151; margin-bottom:.3rem;">
            <span>Progresso de configuração</span>
            <span>{{ $pct }}%</span>
        </div>
        <div class="progresso-bar-wrap">
            <div class="progresso-bar" style="width:{{ $pct }}%;"></div>
        </div>
    </div>

    {{-- Lista de passos --}}
    @php
        $pagamentoFeito = $step->step_pagamento_realizado;
        $stepsExibir    = $step->stepsParaExibir($planoAtivo);
        // Keys que ficam liberadas mesmo sem pagamento
        $stepsLivres = ['step_empresa_configurada', 'step_plano_contratado', 'step_pagamento_realizado'];
    @endphp
    <div class="steps-list">
        @foreach ($stepsExibir as $s)
            @php
                $podeIr    = Route::has($s['rota']);
                $bloqueado = !$pagamentoFeito && !in_array($s['key'], $stepsLivres);
                $classes   = trim(($s['feito'] ? 'feito' : '') . ' ' . ($bloqueado ? 'bloqueado' : ''));
            @endphp
            <div class="step-item {{ $classes }}">
                <div class="step-icon">
                    <i class="fas fa-{{ $bloqueado ? 'lock' : $s['icone'] }}"></i>
                </div>
                <div class="step-body">
                    <div class="step-label">{{ $s['label'] }}</div>
                    <div class="step-desc">{{ $s['descricao'] }}</div>
                    @if ($bloqueado)
                        <span class="step-bloqueado-hint">
                            <i class="fas fa-lock"></i> Disponível após confirmar o pagamento
                        </span>
                    @elseif (!$s['feito'] && $podeIr)
                        <a href="{{ route($s['rota'], $s['params'] ?? []) }}" class="step-btn">
                            <i class="fas fa-arrow-right"></i> Fazer agora
                        </a>
                    @endif
                </div>
                @if ($s['feito'])
                    <i class="fas fa-check-circle step-check"></i>
                @elseif ($bloqueado)
                    <i class="fas fa-lock step-lock"></i>
                @endif
            </div>
        @endforeach
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.75rem;">
        <span style="font-size:.83rem; color:#94a3b8;">
            @if (!$pagamentoFeito)
                Confirme o pagamento para liberar o acesso ao painel.
            @else
                Você pode completar a configuração depois, pelo painel.
            @endif
        </span>
        @if ($pagamentoFeito)
            <form action="{{ route('onboarding.concluir') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg">
                    Ir para o Painel <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        @else
            <button type="button" class="btn btn-primary btn-lg btn-painel-disabled" disabled title="Confirme o pagamento primeiro">
                Ir para o Painel <i class="fas fa-lock ml-1"></i>
            </button>
        @endif
    </div>
</div>
@endsection
