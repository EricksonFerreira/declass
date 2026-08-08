<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page_title', 'Configuração') — DECLOCK</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicons/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@700;800;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: #334155; min-height: 100vh; }
    a { text-decoration: none; color: inherit; }

    /* ── Shell ── */
    .onb-shell { display: flex; flex-direction: column; min-height: 100vh; }
    .onb-header {
        background: #0a1628; color: #fff;
        padding: 1rem 2rem;
        display: flex; align-items: center; justify-content: space-between;
        position: sticky; top: 0; z-index: 100;
        box-shadow: 0 2px 8px rgba(0,0,0,.2);
    }
    .onb-logo { font-family: 'Raleway', sans-serif; font-size: 1.3rem; font-weight: 900; letter-spacing: -.03em; }
    .onb-logo span { color: #38bdf8; }
    .onb-header-meta { font-size: .8rem; color: rgb(255, 255, 255); }

    /* ── Progresso de etapas ── */
    .onb-steps {
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        padding: .75rem 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        overflow-x: auto;
    }
    .onb-step {
        display: flex; align-items: center; gap: .5rem;
        font-size: .78rem; font-weight: 600; color: #94a3b8;
        padding: .35rem .75rem;
        white-space: nowrap;
    }
    .onb-step.done   { color: #22c55e; }
    .onb-step.active { color: #0a1628; font-weight: 700; }
    .onb-step-num {
        width: 22px; height: 22px; border-radius: 50%; border: 2px solid currentColor;
        display: flex; align-items: center; justify-content: center;
        font-size: .7rem; flex-shrink: 0;
    }
    .onb-step.done .onb-step-num  { background: #22c55e; border-color: #22c55e; color: #fff; }
    .onb-step.active .onb-step-num { background: #0a1628; border-color: #0a1628; color: #fff; }
    .onb-step-sep { color: #cbd5e1; font-size: .7rem; padding: 0 .25rem; }

    /* ── Conteúdo principal ── */
    .onb-main {
        flex: 1;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 2.5rem 1rem 4rem;
    }
    .onb-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 2.5rem;
        width: 100%;
        max-width: 780px;
        box-shadow: 0 4px 24px rgba(15,23,42,.06);
    }
    .onb-card-wide { max-width: 1100px; }

    .onb-card-title {
        font-family: 'Raleway', sans-serif;
        font-size: 1.6rem; font-weight: 900; color: #0a1628;
        margin-bottom: .4rem; letter-spacing: -.025em;
    }
    .onb-card-sub { font-size: .9rem; color: #64748b; margin-bottom: 2rem; line-height: 1.6; }

    /* ── Form ── */
    .form-label { font-size: .83rem; font-weight: 600; color: #374151; margin-bottom: .3rem; display: block; }
    .form-control {
        display: block; width: 100%; padding: .65rem .9rem;
        font-size: .92rem; font-family: inherit;
        border: 1.5px solid #e2e8f0; border-radius: 10px;
        background: #f8fafc; color: #0f172a;
        transition: border-color .2s, box-shadow .2s;
        outline: none;
    }
    .form-control:focus { border-color: #0a1628; background: #fff; box-shadow: 0 0 0 3px rgba(10,22,40,.08); }
    .form-control.is-invalid { border-color: #ef4444; }
    .invalid-feedback { color: #ef4444; font-size: .78rem; margin-top: .2rem; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
    @media (max-width: 560px) { .form-row { grid-template-columns: 1fr; } }

    /* ── Botões ── */
    .btn {
        display: inline-flex; align-items: center; gap: .45rem;
        padding: .7rem 1.4rem; border-radius: 10px;
        font-size: .9rem; font-weight: 700; font-family: inherit;
        cursor: pointer; border: none; transition: all .2s;
    }
    .btn-primary { background: #0a1628; color: #fff; }
    .btn-primary:hover { background: #1e3a5f; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(10,22,40,.2); }
    .btn-secondary { background: #f1f5f9; color: #374151; border: 1.5px solid #e2e8f0; }
    .btn-secondary:hover { background: #e2e8f0; }
    .btn-lg { padding: .85rem 2rem; font-size: 1rem; }
    .btn-block { width: 100%; justify-content: center; }

    /* ── Alert ── */
    .alert { padding: .85rem 1rem; border-radius: 10px; font-size: .875rem; margin-bottom: 1.25rem; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
    .alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    .alert-danger  { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; }
    </style>
    @stack('styles')
</head>
<body>
<div class="onb-shell">
    <header class="onb-header">
        <div class="onb-logo">De<span>Cars</span></div>
        <div class="onb-header-meta">Configuração inicial</div>
    </header>

    {{-- Barra de etapas --}}
    @php
        $etapas = [
            ['rota' => 'onboarding.empresa',     'label' => 'Empresa'],
            ['rota' => 'onboarding.plano',        'label' => 'Plano'],
            ['rota' => 'onboarding.pagamento',    'label' => 'Pagamento'],
            ['rota' => 'onboarding.cores',        'label' => 'Cores'],
            ['rota' => 'onboarding.orientacoes',  'label' => 'Orientações'],
        ];
        $etapaAtiva = $etapaAtiva ?? 'onboarding.empresa';
        $etapasConc = $etapasConc ?? [];
    @endphp
    <nav class="onb-steps">
        @foreach ($etapas as $i => $e)
            @php
                $feita  = in_array($e['rota'], $etapasConc);
                $ativa  = $e['rota'] === $etapaAtiva;
                $classe = $feita ? 'done' : ($ativa ? 'active' : '');
            @endphp
            @if ($i > 0)<span class="onb-step-sep"><i class="fas fa-chevron-right"></i></span>@endif
            <div class="onb-step {{ $classe }}">
                <div class="onb-step-num">
                    @if ($feita)<i class="fas fa-check" style="font-size:.65rem;"></i>@else{{ $i + 1 }}@endif
                </div>
                {{ $e['label'] }}
            </div>
        @endforeach
    </nav>

    <main class="onb-main">
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
