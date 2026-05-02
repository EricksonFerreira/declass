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
                            <div class="rs-info-rows">
                                <div class="rs-info-row">
                                    <span>Nome</span>
                                    <strong>{{ $empresa->nome }}</strong>
                                </div>
                                <div class="rs-info-row">
                                    <span>Slug</span>
                                    <strong>{{ $empresa->slug ?? '—' }}</strong>
                                </div>
                                <div class="rs-info-row">
                                    <span>CNPJ</span>
                                    <strong>{{ $empresa->cnpj ?? '—' }}</strong>
                                </div>
                                <div class="rs-info-row">
                                    <span>E-mail</span>
                                    <strong>{{ $empresa->email ?? '—' }}</strong>
                                </div>
                                <div class="rs-info-row">
                                    <span>Telefone</span>
                                    <strong>{{ $empresa->telefone ?? '—' }}</strong>
                                </div>
                                <div class="rs-info-row">
                                    <span>Dominio</span>
                                    <strong>{{ $empresa->dominio ?? '—' }}</strong>
                                </div>
                                <div class="rs-info-row">
                                    <span>Status</span>
                                    <strong>
                                        @if($empresa->ativo === 'S')
                                            <span class="badge badge-success">Ativo</span>
                                        @else
                                            <span class="badge badge-danger">Inativo</span>
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
                                    <strong><span class="badge {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span></strong>
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
                    <div class="rs-info-rows">
                        <div class="rs-info-row">
                            <span>Valor</span>
                            <strong>R$ {{ number_format($planoAtivo->valor_cobrado, 2, ',', '.') }}</strong>
                        </div>
                        <div class="rs-info-row">
                            <span>Periodicidade</span>
                            <strong>{{ $planoAtivo->periodicidade_texto ?? ($planoAtivo->periodicidade === 'anual' ? 'Anual' : 'Mensal') }}</strong>
                        </div>
                        <div class="rs-info-row">
                            <span>Inicio</span>
                            <strong>{{ $planoAtivo->data_inicio?->format('d/m/Y') ?? '—' }}</strong>
                        </div>
                        <div class="rs-info-row">
                            <span>Vencimento</span>
                            <strong>{{ $planoAtivo->data_vencimento?->format('d/m/Y') ?? '—' }}</strong>
                        </div>
                        <div class="rs-info-row">
                            <span>Metodo de cobranca</span>
                            <strong>{{ $planoAtivo->metodo_cobranca_texto ?? ucfirst($planoAtivo->metodo_cobranca ?? '—') }}</strong>
                        </div>
                        @if($planoAtivo->renovacao_automatica)
                        <div class="rs-info-row">
                            <span>Renovacao automatica</span>
                            <strong><span class="badge badge-success">Ativa</span></strong>
                        </div>
                        @endif
                    </div>
                </div>

                @if($plano->itens->count())
                <div class="rs-card">
                    <div class="rs-card__header">
                        <i class="fas fa-list rs-card__icon"></i>
                        <h2>Recursos Inclusos</h2>
                    </div>
                    <ul class="list-unstyled mb-0" style="padding:12px 16px;">
                        @foreach($plano->itens as $item)
                            <li class="mb-1 d-flex align-items-start" style="font-size:.85rem;">
                                <i class="fas fa-{{ $item->incluso ? 'check text-success' : 'times text-muted' }} mr-2 mt-1" style="font-size:.7rem;"></i>
                                {{ $item->descricao }}
                            </li>
                        @endforeach
                    </ul>
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
                                                'pago' => 'badge-success',
                                                'pendente' => 'badge-warning',
                                                'vencido' => 'badge-danger',
                                                'cancelado' => 'badge-secondary',
                                                default => 'badge-info',
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

@push('css')
<style>
.rs-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.rs-table th { padding:9px 12px; text-align:left; font-weight:700; color:#475569; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; border-bottom:2px solid #e2e8f0; background:#f8fafc; }
.rs-table td { padding:9px 12px; color:#334155; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.rs-table tbody tr:last-child td { border-bottom:0; }
.rs-table tbody tr:hover td { background:#f8fafc; }
</style>
@endpush
