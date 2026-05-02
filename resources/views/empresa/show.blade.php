@extends('adminlte::page')

@section('title', $empresa->nome)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $planoAtivo = $empresa->planoAtivo()->with('plano.itens')->first();
    $plano = $planoAtivo?->plano;
    $statusBadge = $planoAtivo?->status_badge ?? ['label' => 'Sem plano', 'class' => 'badge-secondary'];
    $totalUsers = $empresa->users()->count();
    $totalVeiculos = \App\Models\Veiculo::where('empresa_id', $empresa->id)->where('ativo', true)->where('vendido', false)->count();
    $logoUrl = $empresa->logo_url;
    $iniciais = collect(preg_split('/\s+/', trim($empresa->nome)))->filter()->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'EM';
@endphp

<div class="rs-page">

    {{-- HERO --}}
    <div class="rs-hero rs-hero--{{ $empresa->ativo === 'S' ? 'confirmada' : 'cancelada' }}">
        <div class="rs-hero__accent"></div>
        <div class="rs-hero__body">
            <div class="rs-hero__left">
                <div class="rs-hero__eyebrow">
                    <i class="fas fa-building"></i>
                    Empresa #{{ $empresa->id }}
                </div>
                <h1 class="rs-hero__title">{{ $empresa->nome }}</h1>
                <div class="rs-hero__meta">
                    <span><i class="fas fa-globe"></i>{{ $empresa->slug ?: 'Sem slug' }}</span>
                    @if($empresa->cnpj)
                        <span><i class="fas fa-id-card"></i>{{ $empresa->cnpj }}</span>
                    @endif
                    <span><i class="fas fa-calendar-plus"></i>Criada em {{ $empresa->created_at?->format('d/m/Y H:i') ?: '-' }}</span>
                </div>
            </div>
            <div class="rs-hero__center">
    <div class="mt-1">
                                <button type="button" class="btn btn-sm btn-outline-danger w-100" data-toggle="modal" data-target="#modalCapturarCalcao">
                                    <i class="fas fa-hand-holding-usd mr-1"></i> Capturar Valor
                                </button>
                            </div>
            </div>
            <div class="rs-hero__right">
                <span class="rs-status-badge {{ $empresa->ativo === 'S' ? 'is-success' : 'is-danger' }}">
                    <i class="fas {{ $empresa->ativo === 'S' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                    {{ $empresa->ativo === 'S' ? 'ATIVO' : 'INATIVO' }}
                </span>
                <div class="rs-hero__kpis">
                    <div class="rs-kpi">
                        <span>Usuarios</span>
                        <strong>{{ $totalUsers }}</strong>
                    </div>
                    <div class="rs-kpi-divider"></div>
                    <div class="rs-kpi">
                        <span>Veiculos</span>
                        <strong>{{ $totalVeiculos }}</strong>
                    </div>
                    @if($plano)
                        <div class="rs-kpi-divider"></div>
                        <div class="rs-kpi rs-kpi--accent">
                            <span>Plano</span>
                            <strong>{{ $plano->nome }}</strong>
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BOTOES DE ACAO --}}
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('empresa.editar') }}" class="rs-btn rs-btn--primary">
            <i class="fas fa-pen mr-1"></i> Editar
        </a>
        @if($empresa->site_apresentacao_ativo && $plano?->site_vendas)
            <a href="{{ route('empresa.site-apresentacao.edit', $empresa->id) }}" class="rs-btn">
                <i class="fas fa-globe mr-1"></i> Site
            </a>
        @endif
        <a href="{{ route('assinatura.meu-plano') }}" class="rs-btn">
            <i class="fas fa-credit-card mr-1"></i> Assinatura
        </a>
    </div>

    {{-- TABS --}}
    <div class="rs-tabs-wrapper">
        <ul class="nav nav-tabs rs-nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-dados" data-toggle="tab" href="#pane-dados" role="tab">
                    <i class="fas fa-building"></i>
                    <span>Dados</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-plano" data-toggle="tab" href="#pane-plano" role="tab">
                    <i class="fas fa-layer-group"></i>
                    <span>Plano</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-pagamentos" data-toggle="tab" href="#pane-pagamentos" role="tab">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Pagamentos</span>
                </a>
            </li>
        </ul>

        <div class="tab-content rs-tab-content">

            {{-- TAB 1 — DADOS --}}
            <div class="tab-pane fade show active" id="pane-dados" role="tabpanel">
                <div class="rs-grid">
                    <div class="rs-main">

                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-info-circle rs-card__icon"></i>
                                <h2>Informacoes da Empresa</h2>
                            </div>
                            <div class="rs-info-grid">
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-tag"></i> Nome</span>
                                    <strong class="rs-info-card__value">{{ $empresa->nome }}</strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-globe"></i> Slug</span>
                                    <strong class="rs-info-card__value">{{ $empresa->slug ?? '—' }}</strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-id-card"></i> CNPJ</span>
                                    <strong class="rs-info-card__value">{{ $empresa->cnpj ?? '—' }}</strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-envelope"></i> E-mail</span>
                                    <strong class="rs-info-card__value">{{ $empresa->email ?? '—' }}</strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-phone"></i> Telefone</span>
                                    <strong class="rs-info-card__value">{{ $empresa->telefone ?? '—' }}</strong>
                                </div>
                                <div class="rs-info-card">
                                    <span class="rs-info-card__label"><i class="fas fa-check-circle"></i> Status</span>
                                    <strong class="rs-info-card__value">
                                        @if($empresa->ativo === 'S')
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-danger">Inativo</span>
                                        @endif
                                    </strong>
                                </div>
                            </div>
                        </div>

                        @if($empresa->cor_primaria || $empresa->cor_secundaria)
                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-palette rs-card__icon"></i>
                                <h2>Cores da Marca</h2>
                            </div>
                            <div class="d-flex" style="gap:16px;padding:12px 16px;">
                                @if($empresa->cor_primaria)
                                    <div>
                                        <span class="text-muted small">Primaria</span>
                                        <div style="width:48px;height:48px;border-radius:10px;background:{{ $empresa->cor_primaria }};border:1px solid #e2e8f0;margin-top:4px;"></div>
                                        <span style="font-size:.72rem;color:#64748b;">{{ $empresa->cor_primaria }}</span>
                                    </div>
                                @endif
                                @if($empresa->cor_secundaria)
                                    <div>
                                        <span class="text-muted small">Secundaria</span>
                                        <div style="width:48px;height:48px;border-radius:10px;background:{{ $empresa->cor_secundaria }};border:1px solid #e2e8f0;margin-top:4px;"></div>
                                        <span style="font-size:.72rem;color:#64748b;">{{ $empresa->cor_secundaria }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif

                    </div>

                    <div class="rs-sidebar">

                        @if($logoUrl)
                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-image rs-card__icon"></i>
                                <h2>Logo</h2>
                            </div>
                            <div class="text-center p-3">
                                <img src="{{ $logoUrl }}" alt="Logo {{ $empresa->nome }}" style="max-width:100%;max-height:120px;border-radius:8px;">
                            </div>
                        </div>
                        @endif

                        @if($plano)
                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-layer-group rs-card__icon"></i>
                                <h2>Plano Atual</h2>
                            </div>
                            <div class="rs-info-rows">
                                <div class="rs-info-row">
                                    <span>Plano</span>
                                    <strong>{{ $plano->nome }}</strong>
                                </div>
                                <div class="rs-info-row">
                                    <span>Status</span>
                                    <strong><span class="badge text-white {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span></strong>
                                </div>
                                <div class="rs-info-row">
                                    <span>Periodicidade</span>
                                    <strong>{{ $planoAtivo->periodicidade_texto ?? ($planoAtivo->periodicidade === 'anual' ? 'Anual' : 'Mensal') }}</strong>
                                </div>
                                <div class="rs-info-row">
                                    <span>Vencimento</span>
                                    <strong>{{ $planoAtivo->data_vencimento?->format('d/m/Y') ?? '—' }}</strong>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- TAB 2 — PLANO --}}
            <div class="tab-pane fade" id="pane-plano" role="tabpanel">
                @if($plano)
                <div class="rs-card">
                    <div class="rs-card__header">
                        <i class="fas fa-layer-group rs-card__icon"></i>
                        <h2>{{ $plano->nome }}</h2>
                        <span class="ml-auto badge {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>
                    </div>
                    <div class="rs-info-grid">
                        <div class="rs-info-card">
                            <span class="rs-info-card__label"><i class="fas fa-dollar-sign"></i> Valor</span>
                            <strong class="rs-info-card__value">R$ {{ number_format($planoAtivo->valor_cobrado, 2, ',', '.') }}</strong>
                        </div>
                        <div class="rs-info-card">
                            <span class="rs-info-card__label"><i class="fas fa-calendar-alt"></i> Periodicidade</span>
                            <strong class="rs-info-card__value">{{ $planoAtivo->periodicidade_texto ?? ($planoAtivo->periodicidade === 'anual' ? 'Anual' : 'Mensal') }}</strong>
                        </div>
                        <div class="rs-info-card">
                            <span class="rs-info-card__label"><i class="fas fa-play-circle"></i> Inicio</span>
                            <strong class="rs-info-card__value">{{ $planoAtivo->data_inicio?->format('d/m/Y') ?? '—' }}</strong>
                        </div>
                        <div class="rs-info-card">
                            <span class="rs-info-card__label"><i class="fas fa-hourglass-end"></i> Vencimento</span>
                            <strong class="rs-info-card__value">{{ $planoAtivo->data_vencimento?->format('d/m/Y') ?? '—' }}</strong>
                        </div>
                        <div class="rs-info-card">
                            <span class="rs-info-card__label"><i class="fas fa-credit-card"></i> Metodo de cobranca</span>
                            <strong class="rs-info-card__value">{{ $planoAtivo->metodo_cobranca_texto ?? ucfirst($planoAtivo->metodo_cobranca ?? '—') }}</strong>
                        </div>
                        @if($planoAtivo->renovacao_automatica)
                        <div class="rs-info-card">
                            <span class="rs-info-card__label"><i class="fas fa-sync-alt"></i> Renovacao automatica</span>
                            <strong class="rs-info-card__value"><span class="badge bg-success">Ativa</span></strong>
                        </div>
                        @endif
                    </div>
                </div>

                @if($plano->itens->count())
                <div class="rs-card">
                    <div class="rs-card__header">
                        <i class="fas fa-list rs-card__icon"></i>
                        <h2>Recursos Inclusos</h2>
                        <span class="ml-auto badge bg-info">{{ $plano->itens->count() }}</span>
                    </div>
                    <div class="rs-info-grid">
                        @foreach($plano->itens as $item)
                            <div class="rs-info-card" style="display:flex;align-items:center;gap:10px;padding:12px 14px;">
                                <div style="width:28px;height:28px;border-radius:7px;background:{{ $item->incluso ? '#dcfce7' : '#f1f5f9' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas fa-{{ $item->incluso ? 'check' : 'times' }}" style="font-size:.72rem;color:{{ $item->incluso ? '#16a34a' : '#94a3b8' }};"></i>
                                </div>
                                <span style="font-size:.82rem;color:#0f172a;font-weight:500;">{{ $item->descricao }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="d-flex" style="gap:10px;flex-wrap:wrap;padding:4px 0;">
                    @can('gerenciar-planos')
                        <a href="{{ route('assinatura.meu-plano') }}" class="rs-btn rs-btn--primary">
                            <i class="fas fa-credit-card mr-1"></i> Gerenciar assinatura
                        </a>
                    @endcan
                </div>
                @else
                <div class="rs-card">
                    <div class="rs-card__header">
                        <i class="fas fa-box-open rs-card__icon"></i>
                        <h2>Sem plano ativo</h2>
                    </div>
                    <div class="p-3">
                        <p class="text-muted">Esta empresa nao possui um plano ativo no momento.</p>
                        <a href="{{ route('assinatura.meu-plano') }}" class="rs-btn rs-btn--primary">
                            <i class="fas fa-layer-group mr-1"></i> Ver planos
                        </a>
                    </div>
                </div>
                @endif
            </div>

            {{-- TAB 3 — PAGAMENTOS --}}
            <div class="tab-pane fade" id="pane-pagamentos" role="tabpanel">
                @php $pagamentos = $empresa->pagamentosPlano()->orderByDesc('created_at')->limit(20)->get(); @endphp
                @if($pagamentos->count())
                <div class="rs-card">
                    <div class="rs-card__header">
                        <i class="fas fa-history rs-card__icon"></i>
                        <h2>Historico de Pagamentos</h2>
                        <span class="ml-auto badge badge-secondary">{{ $pagamentos->count() }}</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="rs-table">
                            <thead>
                                <tr>
                                    <th>Referencia</th>
                                    <th>Valor</th>
                                    <th>Metodo</th>
                                    <th>Status</th>
                                    <th>Vencimento</th>
                                    <th>Pago em</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pagamentos as $pag)
                                <tr>
                                    <td class="font-weight-bold" style="font-size:.78rem;">{{ $pag->referencia_interna ?? '#' . $pag->id }}</td>
                                    <td>R$ {{ number_format($pag->valor, 2, ',', '.') }}</td>
                                    <td>{{ ucfirst($pag->metodo ?? '—') }}</td>
                                    <td>
                                        @php
                                            $pagBadge = match($pag->status) {
                                                'pago' => 'bg-success',
                                                'pendente' => 'bg-warning',
                                                'vencido' => 'bg-danger',
                                                'cancelado' => 'bg-secondary',
                                                default => 'bg-info',
                                            };
                                        @endphp
                                        <span class="badge {{ $pagBadge }}">{{ ucfirst($pag->status) }}</span>
                                    </td>
                                    <td style="font-size:.82rem;">{{ $pag->data_vencimento?->format('d/m/Y') ?? '—' }}</td>
                                    <td style="font-size:.82rem;">{{ $pag->pago_em?->format('d/m/Y') ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="rs-card">
                    <div class="card-body">
                        <div class="rs-empty">
                            <i class="fas fa-inbox"></i>
                            <span>Nenhum pagamento registrado.</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>

</div>
@stop

@section('css')
<style>
.content-wrapper { background:#f0f4f9; }

.rs-page { display:grid; gap:20px; padding-bottom:32px; }

.rs-hero { position:relative; border-radius:20px; background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%); overflow:hidden; box-shadow:0 8px 32px rgba(15,23,42,.20); }
.rs-hero__accent { position:absolute; top:0;left:0;right:0; height:4px; background:linear-gradient(90deg,#3b82f6,#8b5cf6); }
.rs-hero--confirmada .rs-hero__accent { background:linear-gradient(90deg,#10b981,#34d399); }
.rs-hero--cancelada .rs-hero__accent { background:linear-gradient(90deg,#ef4444,#f87171); }
.rs-hero__body { display:flex; align-items:flex-start; justify-content:space-between; gap:24px; padding:28px 30px; flex-wrap:wrap; }
.rs-hero__left { display:grid; gap:8px; }
.rs-hero__eyebrow { display:flex; align-items:center; gap:6px; font-size:.72rem; font-weight:700; letter-spacing:.15em; text-transform:uppercase; color:rgba(148,163,184,.9); }
.rs-hero__title { margin:0; font-size:1.9rem; font-weight:800; letter-spacing:-.03em; color:#fff; line-height:1.15; }
.rs-hero__meta { display:flex; flex-wrap:wrap; gap:8px 18px; color:rgba(148,163,184,.85); font-size:.84rem; }
.rs-hero__meta span { display:flex; align-items:center; gap:6px; }
.rs-hero__right { display:flex; flex-direction:column; align-items:flex-end; gap:16px; }
.rs-hero__kpis { display:flex; align-items:center; gap:0; background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.1); border-radius:14px; overflow:hidden; }
.rs-kpi { padding:14px 20px; text-align:center; }
.rs-kpi span { display:block; font-size:.68rem; text-transform:uppercase; letter-spacing:.12em; color:rgba(148,163,184,.8); margin-bottom:4px; }
.rs-kpi strong { display:block; font-size:1.1rem; font-weight:800; color:#fff; }
.rs-kpi--accent strong { color:#fbbf24; }
.rs-kpi-divider { width:1px; height:40px; background:rgba(255,255,255,.1); }

.rs-status-badge { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:999px; font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; white-space:nowrap; }
.rs-status-badge.is-success { background:rgba(16,185,129,.15); color:#059669; border:1px solid rgba(16,185,129,.25); }
.rs-status-badge.is-danger { background:rgba(239,68,68,.15); color:#dc2626; border:1px solid rgba(239,68,68,.25); }

.rs-tabs-wrapper { background:#fff; border:1px solid #e2e8f0; border-radius:20px; overflow:hidden; box-shadow:0 2px 12px rgba(15,23,42,.05); }
.rs-nav-tabs { background:#f8fafc; border-bottom:2px solid #e2e8f0; padding:0 16px; flex-wrap:nowrap; overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:none; }
.rs-nav-tabs::-webkit-scrollbar { display:none; }
.rs-nav-tabs .nav-item { flex-shrink:0; }
.rs-nav-tabs .nav-link { display:flex; align-items:center; gap:7px; padding:14px 18px; font-size:.83rem; font-weight:600; color:#64748b; border:none; border-bottom:3px solid transparent; border-radius:0; white-space:nowrap; transition:color .15s,border-color .15s; margin-bottom:-2px; }
.rs-nav-tabs .nav-link:hover { color:#2563eb; border-bottom-color:#bfdbfe; background:transparent; }
.rs-nav-tabs .nav-link.active { color:#2563eb; border-bottom-color:#2563eb; background:transparent; font-weight:700; }
.rs-nav-tabs .nav-link i { font-size:.85rem; }
.rs-tab-content { padding:24px; }

.rs-grid { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(290px,.75fr); gap:20px; align-items:start; }
.rs-main, .rs-sidebar { display:grid; gap:16px; }

.rs-card { background:#fff; border:1px solid #e2e8f0; border-radius:18px; padding:20px 22px; box-shadow:0 2px 12px rgba(15,23,42,.05); }
.rs-card__header { display:flex; align-items:center; gap:10px; margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid #f1f5f9; }
.rs-card__icon { width:32px; height:32px; border-radius:9px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:.82rem; flex-shrink:0; }
.rs-card__header h2 { margin:0; font-size:.92rem; font-weight:700; color:#0f172a; }

.rs-info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; }
.rs-info-card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; transition:border-color .15s,box-shadow .15s; }
.rs-info-card:hover { border-color:#cbd5e1; box-shadow:0 2px 8px rgba(15,23,42,.06); }
.rs-info-card__label { display:flex; align-items:center; gap:6px; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-bottom:6px; }
.rs-info-card__label i { width:14px; text-align:center; color:#94a3b8; font-size:.7rem; }
.rs-info-card__value { display:block; font-size:.88rem; font-weight:600; color:#0f172a; word-break:break-word; }

.rs-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:10px; font-size:.82rem; font-weight:700; border:none; cursor:pointer; text-decoration:none; transition:all .18s; color:#fff; background:#0f172a; }
.rs-btn:hover { color:#fff; text-decoration:none; background:#1e293b; }
.rs-btn--primary { background:#2563eb; }
.rs-btn--primary:hover { background:#1d4ed8; }

.rs-empty { display:flex; flex-direction:column; align-items:center; gap:8px; padding:20px; color:#94a3b8; text-align:center; }
.rs-empty i { font-size:1.8rem; }
.rs-empty span { font-size:.8rem; }

.rs-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.rs-table th { padding:9px 12px; text-align:left; font-weight:700; color:#475569; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; border-bottom:2px solid #e2e8f0; background:#f8fafc; }
.rs-table td { padding:9px 12px; color:#334155; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.rs-table tbody tr:last-child td { border-bottom:0; }
.rs-table tbody tr:hover td { background:#f8fafc; }
</style>
@endsection
