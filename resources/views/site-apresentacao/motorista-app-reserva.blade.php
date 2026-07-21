@php
    $plano = $registro->planoMotoristaApp;
    $estacao = $registro->estacaoRetirada;
    $statusLabel = ucfirst(str_replace(['pendente_', '_'], ['Pendente ', ' '], (string) $registro->status));
    $statusPagamento = $registro->pagamento_efetuado ? 'Pago' : 'Pendente';
    $opcionaisSelecionados = is_array($registro->opcionais_json ?? null) ? $registro->opcionais_json : [];
    $opcionaisMap = $plano?->opcionais?->keyBy('id') ?? collect();
    $codigoReserva = $registro->codigoReserva?->codigo ?? ('#' . $registro->id);
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reserva de motorista de app | {{ $empresa->nome }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin: 0; font-family: Arial, sans-serif; color: #0f172a; background: #eef4fb; }
        .top { background:#fff; border-bottom:1px solid #d7e2ef; }
        .top .inner { width:min(1160px, calc(100% - 24px)); margin:0 auto; min-height:70px; display:flex; align-items:center; justify-content:space-between; }
        .brand { display:flex; align-items:center; gap:10px; text-decoration:none; color:#0f172a; font-weight:700; }
        .brand img { width:42px; height:42px; border-radius:10px; object-fit:contain; background:#173d63; padding:4px; }
        .back { text-decoration:none; border:1px solid #d7e2ef; border-radius:999px; padding:10px 14px; color:#334155; font-size:13px; font-weight:700; background:#fff; }
        .container { width:min(1160px, calc(100% - 24px)); margin:0 auto; }
        .layout { display:grid; grid-template-columns:minmax(0,1fr) 300px; gap:14px; padding:16px 0 26px; }
        .card { background:#fff; border:1px solid #d7e2ef; border-radius:18px; box-shadow:0 18px 40px rgba(15,23,42,.08); overflow:hidden; }
        .head { padding:18px; color:#fff; background:linear-gradient(120deg, #173d63, #0f2740); }
        .head h1 { margin:0 0 8px; font-size:clamp(1.55rem, 2.8vw, 2.25rem); line-height:1.05; }
        .head p { margin:0; font-size:14px; opacity:.94; line-height:1.6; }
        .body { padding:16px 18px 18px; }
        .summary-grid { display:grid; gap:10px; }
        .summary-item { padding:12px 14px; border-radius:14px; background:linear-gradient(180deg, #f8fbff, #fdfefe); border:1px solid #d7e2ef; }
        .summary-item strong { display:block; font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:#64748b; margin-bottom:6px; }
        .summary-item span { font-weight:800; color:#0f172a; }
        .summary-total { margin-top:12px; border-top:1px solid #d7e2ef; padding-top:12px; display:grid; gap:6px; }
        .summary-total__row { display:flex; justify-content:space-between; gap:10px; padding:7px 0; font-size:.94rem; }
        .summary-total__row strong, .summary-total__row span { color:#0f172a; font-weight:800; }
        .selected-optional-list { display:grid; gap:8px; margin-top:12px; }
        .selected-optional-item { padding:10px 12px; border-radius:12px; border:1px solid #d7e2ef; background:#fff; }
        .selected-optional-item__name { display:flex; justify-content:space-between; gap:8px; font-weight:800; color:#0f172a; }
        .selected-optional-item__meta { margin-top:4px; color:#64748b; font-size:.82rem; line-height:1.45; }
        .sidebar { display:grid; gap:14px; align-content:start; }
        .chip { display:inline-flex; align-items:center; padding:7px 11px; border-radius:999px; background:#eef6fb; border:1px solid #d7e2ef; font-size:.78rem; font-weight:700; color:#334155; }
        .actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:14px; }
        .btn { border:0; border-radius:999px; padding:11px 16px; font-weight:800; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
        .btn-secondary { background:#eef4fb; color:#0f172a; }
        .btn-dark { background:#0f172a; color:#fff; }
        .muted { color:#64748b; }
        @media (max-width: 1024px) { .layout { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<header class="top">
    <div class="inner">
        <a href="{{ route('site.apresentacao') }}" class="brand">
            <img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="{{ $empresa->nome }}">
            <span>{{ $empresa->nome }}</span>
        </a>
        <a class="back" href="{{ route('reserva.minhas') }}">Voltar</a>
    </div>
</header>

<main class="container layout">
    <section class="card">
        <div class="head">
            <h1>Reserva de motorista de app</h1>
            <p>Registro criado com base no plano escolhido. O valor já ficou pendente para acompanhamento.</p>
        </div>
        <div class="body">
            @if(session('success'))
                <div class="summary-item" style="margin-bottom:12px;border-color:#bbf7d0;background:#f0fdf4;color:#166534;">
                    {{ session('success') }}
                </div>
            @endif

            <div class="summary-grid">
                <div class="summary-item">
                    <strong>Código da reserva</strong>
                    <span>{{ $codigoReserva }}</span>
                </div>
                <div class="summary-item">
                    <strong>Status</strong>
                    <span>{{ $statusLabel }}</span>
                </div>
                <div class="summary-item">
                    <strong>Pagamento</strong>
                    <span>{{ $statusPagamento }}</span>
                </div>
                <div class="summary-item">
                    <strong>Data para começar</strong>
                    <span>{{ $registro->data_inicio ? \Carbon\Carbon::parse($registro->data_inicio)->format('d/m/Y H:i') : 'Não informada' }}</span>
                </div>
                <div class="summary-item">
                    <strong>Data final</strong>
                    <span>{{ $registro->data_fim ? \Carbon\Carbon::parse($registro->data_fim)->format('d/m/Y H:i') : 'Não informada' }}</span>
                </div>
            </div>

            <div class="summary-total">
                <div class="summary-total__row">
                    <strong>Plano</strong>
                    <span>R$ {{ number_format((float) ($registro->valor_plano ?? 0), 2, ',', '.') }}</span>
                </div>
                <div class="summary-total__row">
                    <strong>Opcionais</strong>
                    <span>R$ {{ number_format((float) ($registro->valor_opcionais ?? 0), 2, ',', '.') }}</span>
                </div>
                <div class="selected-optional-list">
                    @forelse($opcionaisSelecionados as $item)
                        @php
                            $opcional = $opcionaisMap->get((int) ($item['id'] ?? 0));
                        @endphp
                        <div class="selected-optional-item">
                            <div class="selected-optional-item__name">
                                <span>{{ $opcional?->nome ?? ('Opcional #' . (int) ($item['id'] ?? 0)) }}</span>
                                <span>x{{ (int) ($item['quantidade'] ?? 0) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="selected-optional-item">
                            <div class="muted">Nenhum opcional selecionado.</div>
                        </div>
                    @endforelse
                </div>
                <div class="summary-total__row">
                    <strong>Total</strong>
                    <span>R$ {{ number_format((float) ($registro->valor_total_final ?? 0), 2, ',', '.') }}</span>
                </div>
            </div>

            <div class="actions">
                <a href="{{ route('reserva.minhas') }}" class="btn btn-dark">Ver minhas reservas</a>
                <a href="{{ route('site.apresentacao.motorista-app') }}" class="btn btn-secondary">Nova reserva</a>
            </div>
        </div>
    </section>

    <aside class="sidebar">
        <section class="card">
            <div class="body">
                <div class="summary-grid">
                    <div class="summary-item">
                        <strong>Estação de retirada</strong>
                        <span>{{ $estacao?->nome ?? 'Não informada' }}</span>
                    </div>
                    <div class="summary-item">
                        <strong>Plano</strong>
                        <span>{{ $plano?->nome ?? 'Não informado' }}</span>
                    </div>
                    <div class="summary-item">
                        <strong>Solicitação</strong>
                        <span>#{{ $registro->motorista_app_solicitacao_id ?? $registro->id }}</span>
                    </div>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
                    <span class="chip">{{ $registro->status_pagamento === 'pendente' ? 'Pagamento pendente' : 'Pagamento definido' }}</span>
                    <span class="chip">{{ $registro->pagamento_efetuado ? 'Pago' : 'Não pago' }}</span>
                </div>
            </div>
        </section>
    </aside>
</main>
</body>
</html>
