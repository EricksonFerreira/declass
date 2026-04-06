@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="container-fluid px-0">

    {{-- HERO HEADER --}}
    <div class="module-card mb-4">
        <div class="module-card-header">
            <div class="module-header-left">
                <div class="module-header-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <div>
                    <div class="module-header-eyebrow">Manutencao de Frota</div>
                    <h1 class="module-header-title">Ordens de Servico</h1>
                    <p class="module-header-sub">Gerencie todas as manutencoes e reparos dos veiculos da frota</p>
                </div>
            </div>
            <div class="module-header-right">
                <a href="{{ route('ordem-servico.create') }}" class="btn-xp">
                    <i class="fas fa-plus mr-1"></i>Nova OS
                </a>
            </div>
        </div>
        <div class="module-about">
            <div class="module-about-icon"><i class="fas fa-info-circle"></i></div>
            <div class="module-about-content">
                <strong>Ordens de Servico</strong>
                <p>Gerencie todas as manutencoes e reparos dos veiculos. Uma OS pode ser criada manualmente ou a partir de avarias registradas no check-in. Acompanhe o andamento de cada servico, registre resultados por item e conclua a OS para marcar automaticamente as avarias como corrigidas.</p>
            </div>
        </div>
    </div>

    {{-- FILTROS DE STATUS --}}
    <div class="config-card mb-4">
        <div class="config-block">
            <div class="filter-tabs">
                <a href="{{ route('ordem-servico.index') }}"
                   class="filter-tab {{ is_null($status ?? null) ? 'active' : '' }}">
                    <i class="fas fa-list mr-1"></i>Todas
                </a>
                <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_ABERTA]) }}"
                   class="filter-tab {{ ($status ?? null) === \App\Models\OrdemServico::STATUS_ABERTA ? 'active' : '' }}">
                    <i class="fas fa-folder-open mr-1"></i>Abertas
                </a>
                <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_EM_ANDAMENTO]) }}"
                   class="filter-tab {{ ($status ?? null) === \App\Models\OrdemServico::STATUS_EM_ANDAMENTO ? 'active' : '' }}">
                    <i class="fas fa-spinner mr-1"></i>Em Andamento
                </a>
                <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_CONCLUIDA]) }}"
                   class="filter-tab {{ ($status ?? null) === \App\Models\OrdemServico::STATUS_CONCLUIDA ? 'active' : '' }}">
                    <i class="fas fa-check-circle mr-1"></i>Concluidas
                </a>
                <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_CANCELADA]) }}"
                   class="filter-tab {{ ($status ?? null) === \App\Models\OrdemServico::STATUS_CANCELADA ? 'active' : '' }}">
                    <i class="fas fa-ban mr-1"></i>Canceladas
                </a>
            </div>
        </div>
    </div>

    {{-- TABELA --}}
    <div class="config-card">
        <div class="config-block">
            <div class="config-block-label">
                <i class="fas fa-clipboard-list"></i>
                <div>
                    <strong>Lista de Ordens de Servico</strong>
                    <span>{{ $ordens->total() }} OS(s) encontrada(s)</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover xp-table">
                    <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Veiculo</th>
                            <th>Reserva</th>
                            <th>Status</th>
                            <th>Aberta por</th>
                            <th>Atualizacao</th>
                            <th>Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ordens as $os)
                            <tr>
                                <td><span class="xp-id">#{{ $os->numero ?? $os->id }}</span></td>
                                <td>
                                    <a href="{{ route('veiculo.show', $os->veiculo_id) }}" class="xp-link">
                                        <i class="fas fa-car mr-1"></i>{{ optional($os->veiculo)->placa ?? $os->veiculo_id }}
                                    </a>
                                </td>
                                <td>
                                    @if($os->reserva_id)
                                        <a href="{{ route('reserva.show', $os->reserva_id) }}" class="xp-link">
                                            <i class="fas fa-calendar-alt mr-1"></i>#{{ $os->reserva_id }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($os->status === \App\Models\OrdemServico::STATUS_ABERTA)
                                        <span class="status-badge status-badge--amber"><i class="fas fa-folder-open mr-1"></i>Aberta</span>
                                    @elseif($os->status === \App\Models\OrdemServico::STATUS_EM_ANDAMENTO)
                                        <span class="status-badge status-badge--blue"><i class="fas fa-spinner mr-1"></i>Em Andamento</span>
                                    @elseif($os->status === \App\Models\OrdemServico::STATUS_CONCLUIDA)
                                        <span class="status-badge status-badge--green"><i class="fas fa-check-circle mr-1"></i>Concluida</span>
                                    @elseif($os->status === \App\Models\OrdemServico::STATUS_CANCELADA)
                                        <span class="status-badge status-badge--gray"><i class="fas fa-ban mr-1"></i>Cancelada</span>
                                    @else
                                        <span class="status-badge status-badge--gray">{{ $os->status }}</span>
                                    @endif
                                </td>
                                <td>{{ optional($os->usuarioAbertura)->name ?? '—' }}</td>
                                <td>{{ $os->updated_at ? $os->updated_at->format('d/m/Y H:i') : '—' }}</td>
                                <td>
                                    <a href="{{ route('ordem-servico.show', $os) }}" class="btn-xp btn-xp--sm">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-clipboard"></i>
                                        <p>Nenhuma ordem de servico encontrada.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ordens->hasPages())
                <div class="mt-3">
                    {{ $ordens->appends(['status' => $status ?? null])->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --c-ink:    color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 78%, #08111f 22%);
        --c-navy:   color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 72%, var(--empresa-cor-secundaria, #334155) 28%);
        --c-indigo: var(--empresa-cor-primaria, #1F2937);
        --c-sky:    var(--empresa-cor-terciaria, #64748B);
        --c-green:  color-mix(in srgb, var(--empresa-cor-secundaria, #334155) 45%, #10b981 55%);
        --c-amber:  #f59e0b;
        --c-red:    #ef4444;
        --c-slate:  color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 82%, #475569 18%);
        --c-border: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 18%, #d7e0ec 82%);
        --c-bg:     color-mix(in srgb, var(--empresa-cor-secundaria, #334155) 10%, #f5f7fb 90%);
        --c-card:   #ffffff;
        --shadow-sm: 0 2px 8px color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 10%, transparent 90%);
        --shadow-lg: 0 16px 48px color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 16%, transparent 84%);
    }
    .content-wrapper { background: var(--c-bg); }

    .module-card { border-radius: 20px; overflow: hidden; background: var(--c-card); box-shadow: var(--shadow-lg); border: 1px solid var(--c-border); }
    .module-card-header {
        position: relative;
        background: linear-gradient(135deg, var(--c-ink) 0%, var(--c-navy) 58%, color-mix(in srgb, var(--c-sky) 42%, var(--c-navy) 58%) 100%);
        padding: 1.75rem 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; overflow: hidden;
    }
    .module-card-header::before { content: ''; position: absolute; inset: 0; background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px); background-size: 20px 20px; pointer-events: none; }
    .module-card-header::after { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--c-indigo), var(--c-sky), var(--c-green)); }
    .module-header-left { position: relative; display: flex; align-items: center; gap: 16px; z-index: 1; }
    .module-header-right { position: relative; z-index: 1; }
    .module-header-icon { width: 56px; height: 56px; border-radius: 14px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: rgba(255,255,255,0.85); flex-shrink: 0; backdrop-filter: blur(8px); }
    .module-header-eyebrow { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: rgba(148,163,184,0.8); margin-bottom: 3px; }
    .module-header-title { font-size: 1.2rem; font-weight: 800; color: #fff; margin: 0 0 3px; letter-spacing: -0.02em; }
    .module-header-sub { font-size: 0.78rem; color: rgba(148,163,184,0.75); margin: 0; }

    .module-about { display: flex; gap: 14px; padding: 1.25rem 2rem; background: color-mix(in srgb, var(--c-sky) 5%, #ffffff 95%); border-top: 1px solid var(--c-border); align-items: flex-start; }
    .module-about-icon { width: 36px; height: 36px; border-radius: 10px; background: color-mix(in srgb, var(--c-indigo) 12%, #ffffff 88%); display: flex; align-items: center; justify-content: center; color: var(--c-indigo); font-size: 1rem; flex-shrink: 0; margin-top: 2px; }
    .module-about-content strong { display: block; font-size: 0.9rem; color: #1e293b; margin-bottom: 4px; }
    .module-about-content p { font-size: 0.82rem; color: var(--c-slate); margin: 0; line-height: 1.6; }

    .config-card { border-radius: 18px; overflow: hidden; background: var(--c-card); box-shadow: var(--shadow-sm); border: 1px solid var(--c-border); }
    .config-block { padding: 1.5rem; border-left: 3px solid var(--c-indigo); margin: 1.25rem; background: color-mix(in srgb, var(--c-sky) 4%, #ffffff 96%); border-radius: 0 12px 12px 0; }
    .config-block-label { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 1rem; }
    .config-block-label > i { margin-top: 3px; color: var(--c-indigo); font-size: 1rem; }
    .config-block-label strong { display: block; font-size: 0.9rem; color: #1e293b; }
    .config-block-label span { font-size: 0.78rem; color: var(--c-slate); }

    .filter-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .filter-tab { display: inline-flex; align-items: center; padding: 7px 16px; border-radius: 8px; font-size: 0.82rem; font-weight: 600; color: var(--c-slate); background: #fff; border: 1px solid var(--c-border); text-decoration: none; transition: all 0.2s; }
    .filter-tab:hover { color: var(--c-indigo); border-color: var(--c-indigo); text-decoration: none; }
    .filter-tab.active { background: linear-gradient(135deg, var(--c-indigo) 0%, var(--c-sky) 100%); color: #fff; border-color: transparent; box-shadow: 0 4px 12px color-mix(in srgb, var(--c-indigo) 28%, transparent 72%); }

    .xp-table { margin: 0; font-size: 0.83rem; }
    .xp-table thead th { background: color-mix(in srgb, var(--c-sky) 10%, #f8fafc 90%); color: var(--c-ink); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--c-border); padding: 10px 12px; }
    .xp-table tbody td { padding: 10px 12px; vertical-align: middle; border-color: #eef2f9; }
    .xp-table tbody tr:hover { background: #f8faff; }
    .xp-id { font-weight: 700; color: var(--c-slate); font-size: 0.78rem; }
    .xp-link { color: var(--c-indigo); font-weight: 600; text-decoration: none; font-size: 0.82rem; }
    .xp-link:hover { color: #3730a3; text-decoration: underline; }

    .status-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 0.73rem; font-weight: 700; }
    .status-badge--green { background: #d1fae5; color: #065f46; }
    .status-badge--amber { background: #fef3c7; color: #92400e; }
    .status-badge--blue  { background: #dbeafe; color: #1e40af; }
    .status-badge--gray  { background: #f1f5f9; color: #475569; }

    .btn-xp { display: inline-flex; align-items: center; padding: 8px 18px; border-radius: 8px; background: linear-gradient(135deg, var(--c-indigo) 0%, var(--c-sky) 100%); color: #fff; font-size: 0.82rem; font-weight: 700; border: none; cursor: pointer; transition: opacity 0.2s, transform 0.2s; box-shadow: 0 4px 12px color-mix(in srgb, var(--c-indigo) 30%, transparent 70%); text-decoration: none; }
    .btn-xp:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; text-decoration: none; }
    .btn-xp--sm { padding: 5px 12px; font-size: 0.76rem; }

    .empty-state { display: flex; flex-direction: column; align-items: center; gap: 10px; color: var(--c-slate); padding: 1rem; }
    .empty-state i { font-size: 2.5rem; opacity: 0.35; }
    .empty-state p { margin: 0; font-size: 0.85rem; }
</style>
@stop

@section('js')
@stop
