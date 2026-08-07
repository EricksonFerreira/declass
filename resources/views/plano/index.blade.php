@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    {{-- <x-page-title :title="$titulo" :subtitle="$subtitulo">
        <a href="{{ route('planos.hub') }}" class="btn btn-xs btn-outline-secondary">
            <i class="fas fa-th-large mr-1"></i> Central
        </a>
        <a href="{{ route('plano.create') }}" class="btn btn-xs btn-primary">
            <i class="fa fa-plus" style="font-size:10px"></i> Novo Plano
        </a>
    </x-page-title> --}}
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="plano-shell">
        <div class="module-card mb-3">
            <div class="module-card-header">
                <div class="module-header-left">
                    <div class="module-header-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <div class="module-header-eyebrow">Gestão de planos</div>
                        <h1 class="module-header-title">Planos cadastrados</h1>
                        <p class="module-header-sub">Organize os planos, revise valores e mantenha as permissões da plataforma consistentes.</p>
                    </div>
                </div>

                <div class="module-header-right">
                    <a href="{{ route('planos.hub') }}" class="btn-xp btn-xp--outline-light btn-xp--sm">
                        <i class="fas fa-th-large"></i> Central
                    </a>
                    <a href="{{ route('plano.create') }}" class="btn-xp btn-xp--sm">
                        <i class="fas fa-plus"></i> Novo plano
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-xl-4 col-md-6 mb-2">
                <div class="kpi-card kpi-total">
                    <div class="kpi-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Planos</span>
                        <strong class="kpi-value">{{ $planos->count() }}</strong>
                        <small class="kpi-subtitle">Cadastros no catálogo</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-2">
                <div class="kpi-card kpi-available">
                    <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Ativos</span>
                        <strong class="kpi-value">{{ $planos->where('ativo', true)->count() }}</strong>
                        <small class="kpi-subtitle">Disponíveis para contratação</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12 mb-2">
                <div class="kpi-card kpi-partial">
                    <div class="kpi-icon"><i class="fas fa-users"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Assinaturas</span>
                        <strong class="kpi-value">{{ $planos->sum('assinaturas_count') }}</strong>
                        <small class="kpi-subtitle">Planos vinculados a empresas</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="config-card shadow-sm border-0">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Lista operacional</span>
                    <h4 class="vs-panel-head__title mb-1">Catálogo de planos</h4>
                    <p class="vs-panel-head__sub mb-0">O acesso aos detalhes segue pelo card e as ações rápidas ficam na lateral de cada item.</p>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="plano-index-grid">
                    @forelse ($planos as $plano)
                        @php
                            $storageMb = $plano->limite_armazenamento_mb ?? 0;
                            $storageTxt = $storageMb >= 1024 ? number_format($storageMb / 1024, 1, ',', '.') . ' GB' : $storageMb . ' MB';
                            $adicionais = collect([
                                ['label' => 'Veículo', 'valor' => $plano->valor_veiculo_adicional ?? null],
                                ['label' => 'Filial', 'valor' => $plano->valor_filial_adicional ?? null],
                                ['label' => 'Armazenamento', 'valor' => $plano->valor_armazenamento_adicional ?? null],
                            ])->filter(fn ($item) => filled($item['valor']));
                        @endphp
                        <article class="plano-card {{ $plano->destaque ? 'plano-card--featured' : '' }}" onclick="window.location='{{ route('plano.show', $plano) }}';" role="link" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){window.location='{{ route('plano.show', $plano) }}';}">
                            <div class="plano-card__accent"></div>
                            <div class="plano-card__head">
                                <div>
                                    <div class="plano-card__name">{{ $plano->nome }}</div>
                                    <div class="plano-card__slug">{{ $plano->slug }}</div>
                                </div>
                                <div class="plano-card__status">
                                    <span class="badge badge-{{ $plano->ativo ? 'success' : 'secondary' }}">
                                        {{ $plano->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>

                            <div class="plano-card__prices">
                                <div>
                                    <span>Mensal</span>
                                    <strong>R$ {{ number_format((float) $plano->valor_mensal, 2, ',', '.') }}</strong>
                                </div>
                                <div>
                                    <span>Anual</span>
                                    <strong>R$ {{ number_format((float) $plano->valor_anual, 2, ',', '.') }}</strong>
                                </div>
                            </div>

                            <div class="plano-card__meta">
                                <div><i class="fas fa-car"></i> {{ $plano->limite_veiculos_texto }}</div>
                                <div><i class="fas fa-database"></i> {{ $storageTxt }}</div>
                                <div><i class="fas fa-users"></i> {{ $plano->assinaturas_count }} assinatura(s)</div>
                                <div><i class="fas fa-user-shield"></i> {{ $plano->limite_permissoes_por_regra ? $plano->limite_permissoes_por_regra . ' permissões' : 'Sem limite de permissões' }}</div>
                            </div>

                            @if($adicionais->isNotEmpty())
                                <div class="plano-card__addons">
                                    @foreach ($adicionais as $item)
                                        <span class="plano-addon-pill">{{ $item['label'] }}: R$ {{ number_format((float) $item['valor'], 2, ',', '.') }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="plano-card__features">
                                <span class="plano-feature {{ $plano->site_vendas ? 'is-on' : 'is-off' }}"><i class="fas fa-globe"></i> Site</span>
                                <span class="plano-feature {{ $plano->modulo_multas ? 'is-on' : 'is-off' }}"><i class="fas fa-file-invoice"></i> Multas</span>
                                <span class="plano-feature {{ $plano->modulo_nfse ? 'is-on' : 'is-off' }}"><i class="fas fa-receipt"></i> NFS-e</span>
                                <span class="plano-feature {{ $plano->multi_estacao ? 'is-on' : 'is-off' }}"><i class="fas fa-map-marker-alt"></i> Filiais</span>
                            </div>

                            <div class="plano-card__actions" onclick="event.stopPropagation();">
                                <a href="{{ route('plano.edit', $plano) }}" class="btn btn-xs btn-outline-secondary">
                                    <i class="fas fa-edit mr-1"></i> Editar
                                </a>
                                <a href="{{ route('plano.acessos', $plano) }}" class="btn btn-xs btn-outline-primary">
                                    <i class="fas fa-user-shield mr-1"></i> Acessos
                                </a>
                                @if ($plano->assinaturas_count === 0)
                                    <form action="{{ route('plano.destroy', $plano) }}" method="POST" onsubmit="return confirm('Remover o plano {{ addslashes($plano->nome) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="plano-empty">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>Nenhum plano cadastrado ainda.</strong>
                                <div>Crie o primeiro plano para começar.</div>
                            </div>
                            <a href="{{ route('plano.create') }}" class="btn btn-primary btn-sm ml-auto">Criar plano</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .plano-shell {
        padding-bottom: 8px;
    }

    .module-card {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(12, 20, 40, .10);
        background: #fff;
    }

    .module-card-header {
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        position: relative;
        overflow: hidden;
    }

    .module-card-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255, 255, 255, 0.055) 1px, transparent 1px);
        background-size: 20px 20px;
        pointer-events: none;
    }

    .module-card-header::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981);
    }

    .module-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }

    .module-header-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #60a5fa;
        flex-shrink: 0;
        backdrop-filter: blur(8px);
    }

    .module-header-eyebrow {
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: rgba(148, 163, 184, .8);
        display: block;
        margin-bottom: 3px;
    }

    .module-header-title {
        font-size: 1.45rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 3px;
        letter-spacing: -.02em;
    }

    .module-header-sub {
        margin: 0;
        font-size: .82rem;
        color: rgba(148, 163, 184, .9);
    }

    .module-header-right {
        position: relative;
        z-index: 1;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-xp {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #4338ca, #6366f1);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 9px 16px;
        font-size: .82rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: all .18s;
        letter-spacing: -.01em;
        box-shadow: 0 4px 12px rgba(67, 56, 202, .25);
    }

    .btn-xp--outline-light {
        background: rgba(255, 255, 255, .12) !important;
        border: 1px solid rgba(255, 255, 255, .25);
        color: rgba(255, 255, 255, .9);
        box-shadow: none;
    }

    .btn-xp--sm {
        padding: 6px 12px;
        font-size: .78rem;
        border-radius: 8px;
    }

    .config-card {
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e2e9f4;
        box-shadow: 0 2px 12px rgba(12, 20, 40, .06);
    }

    .vs-panel-head {
        background: #fff;
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        border-bottom: 1px solid #e7edf6;
    }

    .vs-panel-head__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: #64748b;
    }

    .vs-panel-head__title {
        margin: 0 0 6px;
        font-size: 1.08rem;
        font-weight: 800;
        color: #0c1428;
    }

    .vs-panel-head__sub {
        margin: 0;
        color: #64748b;
        font-size: .84rem;
        max-width: 680px;
    }

    .kpi-card {
        width: 100%;
        border: 0;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        color: #0f172a;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
        min-height: 96px;
        background: #fff;
    }

    .kpi-card .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex: 0 0 auto;
        color: #fff;
    }

    .kpi-meta .kpi-label {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
        opacity: .9;
        font-weight: 700;
    }

    .kpi-meta .kpi-value {
        display: block;
        font-size: 28px;
        line-height: 1.05;
        margin-top: 2px;
    }

    .kpi-subtitle {
        display: block;
        color: #475569;
        margin-top: 2px;
    }

    .kpi-total { background: linear-gradient(135deg, #e2e8f0, #cbd5e1); }
    .kpi-total .kpi-icon { background: #94a3b8; }
    .kpi-available { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
    .kpi-available .kpi-icon { background: #16a34a; }
    .kpi-partial { background: linear-gradient(135deg, #fef3c7, #fde68a); }
    .kpi-partial .kpi-icon { background: #f59e0b; }

    .plano-index-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 14px;
        padding: 16px;
    }

    .plano-card {
        position: relative;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease;
        display: grid;
        gap: 12px;
    }

    .plano-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.10);
    }

    .plano-card--featured {
        border-color: #2563eb;
        box-shadow: 0 14px 30px rgba(37, 99, 235, 0.10);
    }

    .plano-card__accent {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        border-radius: 18px 18px 0 0;
        background: linear-gradient(90deg, #2563eb 0%, #38bdf8 100%);
    }

    .plano-card__head {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
    }

    .plano-card__name {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .plano-card__slug {
        font-size: .78rem;
        color: #64748b;
        margin-top: 2px;
    }

    .plano-card__prices {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .plano-card__prices > div,
    .plano-card__addons {
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
    }

    .plano-card__prices span,
    .plano-addon-pill {
        display: block;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        margin-bottom: 4px;
        font-weight: 700;
    }

    .plano-card__prices strong {
        font-size: 1.08rem;
        font-weight: 800;
        color: #0f172a;
    }

    .plano-card__meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px 12px;
        font-size: .82rem;
        color: #334155;
    }

    .plano-card__meta div {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .plano-card__meta i {
        color: #2563eb;
        width: 14px;
        text-align: center;
    }

    .plano-card__addons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .plano-addon-pill {
        margin: 0;
        padding: 6px 8px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #dbeafe;
    }

    .plano-card__features {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .plano-feature {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .plano-feature.is-on {
        background: #dcfce7;
        color: #166534;
    }

    .plano-feature.is-off {
        background: #f1f5f9;
        color: #64748b;
    }

    .plano-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .plano-card__actions form {
        margin: 0;
    }

    .plano-empty {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 16px;
        padding: 16px;
        color: #1e3a8a;
    }

    .plano-empty i {
        font-size: 1.4rem;
    }

    @media (max-width: 991.98px) {
        .module-card-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .module-header-right {
            width: 100%;
        }

        .module-header-right .btn-xp {
            flex: 1 1 auto;
            justify-content: center;
        }

        .vs-panel-head {
            padding-left: 14px;
            padding-right: 14px;
        }

        .plano-card__meta {
            grid-template-columns: 1fr;
        }
    }
</style>
@stop
