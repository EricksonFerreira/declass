@php
    $etapaAtiva = 'onboarding.pagamento';
    $etapasConc = ['onboarding.empresa', 'onboarding.plano'];
@endphp

@extends('onboarding._layout')
@section('page_title', 'Ajustar Frota')

@push('styles')
<style>
.veiculo-item {
    display: flex; align-items: center; gap:.9rem;
    padding: .85rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 10px;
    cursor: pointer; transition: border-color .2s, background .2s;
    margin-bottom: .6rem;
}
.veiculo-item:hover { border-color: #0a1628; background: #f8fafc; }
.veiculo-item.marcado { border-color: #ef4444; background: #fef2f2; }
.veiculo-item input[type=checkbox] { width:18px; height:18px; flex-shrink:0; accent-color: #ef4444; }
.veiculo-info { flex: 1; }
.veiculo-nome { font-weight: 600; font-size: .9rem; color: #0f172a; }
.veiculo-meta { font-size: .78rem; color: #64748b; }
.veiculo-placa { font-size: .75rem; font-weight: 700; background: #f1f5f9; padding: .1rem .4rem; border-radius: 6px; color: #374151; }
.contador-wrap { background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; padding: .85rem 1rem; margin-bottom: 1.25rem; }
.contador-titulo { font-weight: 700; color: #92400e; font-size: .875rem; }
.contador-texto { font-size: .82rem; color: #78350f; margin-top: .2rem; }
#contadorSelecionados { font-weight: 900; font-size: 1.1rem; color: #dc2626; }
</style>
@endpush

@section('content')
<div class="onb-card" style="max-width:660px;">
    <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:.5rem;">
        <div style="width:40px;height:40px;border-radius:10px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-exclamation-triangle" style="color:#ef4444;font-size:1.1rem;"></i>
        </div>
        <h1 class="onb-card-title" style="margin:0;">Ajuste necessário na frota</h1>
    </div>
    <p class="onb-card-sub">
        O plano <strong>{{ $plano->nome }}</strong> permite até <strong>{{ $plano->limite_veiculos_texto }}</strong> veículo(s).
        Sua frota ativa tem <strong>{{ $todos->count() }}</strong> veículos.
        Você precisa desativar pelo menos <strong>{{ $excedentes->count() }}</strong> veículo(s) para continuar.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $e)<div><i class="fas fa-exclamation-circle mr-1"></i>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="contador-wrap">
        <div class="contador-titulo">
            <i class="fas fa-info-circle mr-1"></i>
            Veículos selecionados para desativar: <span id="contadorSelecionados">0</span> de {{ $excedentes->count() }} necessários
        </div>
        <div class="contador-texto">
            Os veículos desativados não serão excluídos — você poderá reativá-los se fizer upgrade de plano no futuro.
        </div>
    </div>

    <form action="{{ route('onboarding.downgrade.confirmar') }}" method="POST" id="formDowngrade">
        @csrf

        <div style="margin-bottom:.6rem; font-size:.8rem; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:.05em;">
            Selecione os veículos a desativar
        </div>

        @foreach ($todos as $v)
            <label class="veiculo-item" for="v{{ $v->id }}">
                <input type="checkbox" name="veiculos_desativar[]" value="{{ $v->id }}" id="v{{ $v->id }}"
                       onchange="atualizarContador()"
                       {{ $excedentes->contains('id', $v->id) ? '' : '' }}>
                <div class="veiculo-info">
                    <div class="veiculo-nome">{{ $v->nome }}</div>
                    <div class="veiculo-meta">
                        {{ $v->grupoFrota?->nome ?? '—' }}
                        @if ($v->placa_licenca)
                            <span class="veiculo-placa ml-1">{{ $v->placa_licenca }}</span>
                        @endif
                    </div>
                </div>
            </label>
        @endforeach

        <div id="errVeiculos" class="alert alert-danger" style="display:none; margin-top:.75rem;">
            <i class="fas fa-exclamation-circle mr-1"></i> <span id="errVeiculosMsg"></span>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.75rem; margin-top:1.5rem;">
            <a href="{{ route('onboarding.plano') }}" style="font-size:.875rem; color:#64748b;">
                <i class="fas fa-arrow-left mr-1"></i> Escolher outro plano
            </a>
            <button type="button" onclick="confirmarDowngrade()" class="btn btn-primary btn-lg">
                <i class="fas fa-check mr-1"></i> Confirmar e Continuar
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const necessario = {{ $excedentes->count() }};
const limite     = {{ $plano->limite_veiculos ?? 9999 }};
const totalAtivo = {{ $todos->count() }};

function atualizarContador() {
    const selecionados = document.querySelectorAll('input[name="veiculos_desativar[]"]:checked').length;
    document.getElementById('contadorSelecionados').textContent = selecionados;

    // Feedback visual nos itens marcados
    document.querySelectorAll('.veiculo-item').forEach(item => {
        const cb = item.querySelector('input[type=checkbox]');
        item.classList.toggle('marcado', cb.checked);
    });
}

function confirmarDowngrade() {
    const selecionados = document.querySelectorAll('input[name="veiculos_desativar[]"]:checked').length;
    const restantes = totalAtivo - selecionados;

    if (restantes > limite) {
        const faltam = restantes - limite;
        document.getElementById('errVeiculosMsg').textContent =
            `Você ainda precisa desativar mais ${faltam} veículo(s). Atualmente ${restantes} ficariam ativos.`;
        document.getElementById('errVeiculos').style.display = 'block';
        return;
    }

    document.getElementById('errVeiculos').style.display = 'none';
    document.getElementById('formDowngrade').submit();
}
</script>
@endpush
