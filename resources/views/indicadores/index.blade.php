@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body, .wrapper, .content-wrapper { background: #f0f2f7 !important; }

    .ind-header {
        margin-bottom: 28px;
    }
    .ind-header h1 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 6px;
    }
    .ind-header p {
        font-size: 13px;
        color: #64748b;
        margin: 0;
    }

    .ind-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 18px;
    }

    .ind-card {
        background: #fff;
        border: 1px solid #e2e5ef;
        border-radius: 20px;
        padding: 24px 22px 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        text-decoration: none !important;
        color: inherit !important;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        opacity: 0;
        animation: fadeUp 0.35s ease forwards;
    }
    .ind-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.10);
    }
    .ind-card.locked {
        opacity: 0.45 !important;
        pointer-events: none;
        filter: grayscale(0.6);
    }
    .ind-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        border-radius: 20px 20px 0 0;
        background: var(--accent);
    }

    .ind-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: var(--accent-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--accent);
        flex-shrink: 0;
    }

    .ind-name {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }

    .ind-desc {
        font-size: 12px;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }

    .ind-arrow {
        font-size: 11px;
        font-weight: 700;
        color: var(--accent);
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: auto;
    }

    .c-teal    { --accent: #0891b2; --accent-soft: #e0f5fa; }
    .c-emerald { --accent: #059669; --accent-soft: #d1fae5; }
    .c-indigo  { --accent: #4f46e5; --accent-soft: #e0e7ff; }
    .c-amber   { --accent: #d97706; --accent-soft: #fef3c7; }
    .c-rose    { --accent: #e11d48; --accent-soft: #ffe4e6; }
    .c-purple  { --accent: #7c3aed; --accent-soft: #ede9fe; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .ind-grid .ind-card:nth-child(1) { animation-delay: 0.04s; }
    .ind-grid .ind-card:nth-child(2) { animation-delay: 0.08s; }
    .ind-grid .ind-card:nth-child(3) { animation-delay: 0.12s; }
    .ind-grid .ind-card:nth-child(4) { animation-delay: 0.16s; }
    .ind-grid .ind-card:nth-child(5) { animation-delay: 0.20s; }
    .ind-grid .ind-card:nth-child(6) { animation-delay: 0.24s; }

    .lock-badge {
        position: absolute;
        top: 12px; right: 12px;
        background: #f1f5f9;
        border: 1px solid #e2e5ef;
        border-radius: 999px;
        padding: 3px 8px;
        font-size: 10px;
        font-weight: 700;
        color: #94a3b8;
    }
</style>
@stop

@section('content')
<div class="ind-header">
    <h1><i class="fas fa-chart-bar mr-2" style="color:#4f46e5;"></i>Indicadores</h1>
    <p>Selecione um painel para analisar os dados da sua operação.</p>
</div>

<div class="ind-grid">
    @foreach($indicadores as $ind)
        @php $temAcesso = $homeSections[$ind['secao']] ?? false; @endphp
        <a href="{{ $temAcesso ? route('indicadores.show', $ind['slug']) : '#' }}"
           class="ind-card {{ $ind['cor'] }} {{ !$temAcesso ? 'locked' : '' }}">

            @if(!$temAcesso)
                <span class="lock-badge"><i class="fas fa-lock mr-1"></i>Sem acesso</span>
            @endif

            <div class="ind-icon">
                <i class="{{ $ind['icone'] }}"></i>
            </div>
            <div>
                <p class="ind-name">{{ $ind['nome'] }}</p>
                <p class="ind-desc">{{ $ind['descricao'] }}</p>
            </div>
            @if($temAcesso)
                <span class="ind-arrow">Ver indicador <i class="fas fa-arrow-right"></i></span>
            @endif
        </a>
    @endforeach
</div>
@stop
