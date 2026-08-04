@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[[ 'name' => 'Veículos', 'url' => route('veiculo.index') ], [ 'name' => 'Manutenção preventiva', 'url' => route('manutencao.preventivas.index') ], [ 'name' => 'Planos de manutenção' ]]" />
@stop

@section('content')
    @if(isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters ?? []" :filters="$filters" :itensFiltered="$itensFiltered ?? false" />
    @endif

    <div class="container-fluid px-0 vehicle-show-page manutencao-preventiva-shell">
        <div class="module-card mb-3">
            <div class="module-card-header vehicle-hero-compact">
                <div class="module-header-left">
                    <div class="module-header-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <div class="module-header-eyebrow">Veículos · Manutenção preventiva</div>
                        <h1 class="module-header-title">Planos de manutenção</h1>
                        <p class="module-header-sub">Configure, revise e compare planos com vínculo por veículo ou por grupo de frota.</p>
                    </div>
                </div>
                <div class="module-header-right">
                    <a href="{{ route('manutencao.planos.create') }}" class="btn-xp btn-xp--sm">
                        <i class="fas fa-plus"></i> Novo plano
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-total">
                    <div class="kpi-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Planos</span>
                        <strong class="kpi-value">{{ $planos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $planos->total() : count($planos) }}</strong>
                        <small class="kpi-subtitle">Cadastros ativos e históricos</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-available">
                    <div class="kpi-icon"><i class="fas fa-car"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Com veículo</span>
                        <strong class="kpi-value">{{ $planos->whereNotNull('veiculo_id')->count() }}</strong>
                        <small class="kpi-subtitle">Planos amarrados a uma unidade</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-partial">
                    <div class="kpi-icon"><i class="fas fa-sitemap"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Com grupo</span>
                        <strong class="kpi-value">{{ $planos->whereNotNull('grupo_frota_id')->count() }}</strong>
                        <small class="kpi-subtitle">Planos por modelo de frota</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="config-card shadow-sm border-0">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Lista operacional</span>
                    <h4 class="vs-panel-head__title mb-1">Planos cadastrados</h4>
                    <p class="vs-panel-head__sub mb-0">Veja rapidamente a composição dos planos e abra os detalhes dos itens quando precisar comparar a configuração.</p>
                </div>
                <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                    <form action="{{ route('manutencao.planos.setPerPage') }}" method="POST" class="mb-0">
                        @csrf
                        <x-per-page-selector :perPages="$perPages ?? [15, 30, 50, 100]" :selectedPerPage="$perPage ?? 15" />
                    </form>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive vehicle-table-wrap">
                    <table class="table table-hover mb-0 vehicle-list-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Grupo de Frota</th>
                                <th>Itens</th>
                        <th class="d-none d-lg-table-cell">Criado por</th>
                        <th class="d-none d-lg-table-cell">Atualizado por</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($planos as $plano)
                                @php
                                    $collapseId = 'plano-itens-'.$plano->id;
                                @endphp
                                <tr>
                                    <td>{{ $plano->id }}</td>
                                    <td class="font-weight-bold">{{ $plano->nome }}</td>
                                    <td>{{ $plano->grupoFrota->nome ?? '-' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-outline-primary" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false">
                                            <i class="fas fa-list mr-1"></i>{{ $plano->itens->count() }} item(ns)
                                        </button>
                                    </td>
                                    <td class="d-none d-lg-table-cell">{{ $plano->usuarioInclusao?->nome ?? '-' }}</td>
                                    <td class="d-none d-lg-table-cell">{{ $plano->usuarioAtualizacao?->nome ?? '-' }}</td>
                                    <td class="text-center">
                                        <div class="d-flex flex-wrap justify-content-center" style="gap:.35rem;">
                                            <a href="{{ route('manutencao.planos.edit', $plano->id) }}" class="btn btn-xs btn-warning">
                                                <i class="fa fa-edit"></i> Editar
                                            </a>
                                            <x-delete-button :action="route('manutencao.planos.destroy', $plano->id)" />
                                        </div>
                                    </td>
                                </tr>
                                <tr class="collapse bg-light" id="{{ $collapseId }}">
                                    <td colspan="7">
                                        <div class="p-2">
                                            @if($plano->itens->isNotEmpty())
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0 bg-white">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th>Item</th>
                                                                <th>Meses</th>
                                                                <th>KM</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($plano->itens as $itemPlano)
                                                                <tr>
                                                                    <td>{{ $itemPlano->item?->nome ?? ('Item #'.$itemPlano->item_id) }}</td>
                                                                    <td>{{ $itemPlano->frequencia_meses ? $itemPlano->frequencia_meses . ' meses' : '-' }}</td>
                                                                    <td>{{ $itemPlano->frequencia_km ? number_format((float) $itemPlano->frequencia_km, 0, ',', '.') . ' km' : '-' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-muted">Este plano ainda não possui itens vinculados.</div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Nenhum plano cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($planos instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="card-footer d-flex justify-content-end">
                    {{ $planos->links() }}
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
    <script></script>
@stop

@section('css')
<style>
    .manutencao-preventiva-shell { padding-bottom: 8px; }
    .module-card,
    .config-card {
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }
    .module-card {
        box-shadow: 0 16px 44px rgba(15, 23, 42, .08);
    }
    .module-card-header,
    .vs-panel-head {
        background: linear-gradient(135deg, #f8fafc 0%, #eef4ff 50%, #f8fbff 100%);
    }
    .vehicle-table-wrap {
        max-height: none;
        overflow: auto;
    }
    .vehicle-list-table {
        min-width: 900px;
        margin-bottom: 0;
    }
    .vehicle-list-table thead th {
        white-space: nowrap;
        background: #f8fafc;
        font-weight: 800;
        color: #334155;
        border-bottom: 1px solid #e2e8f0;
    }
    .vehicle-list-table tbody td { vertical-align: middle; }
    .vehicle-list-table tbody tr:hover { background: #f8fbff; }
    .kpi-card {
        border-radius: 18px;
        padding: 1rem 1.1rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
    }
    .kpi-label {
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 800;
        font-size: .72rem;
    }
    .kpi-value { font-size: 1.5rem; line-height: 1.1; }
    .kpi-subtitle { color: #64748b; font-size: .78rem; }
    .manutencao-card {
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 16px 44px rgba(15, 23, 42, .08);
    }
    .manutencao-card__header {
        background: linear-gradient(135deg, #f8fafc 0%, #eef4ff 50%, #f8fbff 100%);
    }
    .manutencao-card__badge,
    .manutencao-card__tip {
        border-radius: 14px;
    }
    .manutencao-card .form-control,
    .manutencao-card .form-select {
        border-radius: 12px;
        border-color: #dbe4ef;
        min-height: 42px;
    }
    .manutencao-card .form-control:focus,
    .manutencao-card .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .15);
    }
    .manutencao-section {
        border-radius: 18px;
        border: 1px solid #e5eaf2;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .04);
    }
    .manutencao-table-wrap {
        border-radius: 12px;
        overflow: hidden;
    }
    .manutencao-table-wrap .table {
        min-width: 640px;
    }
    @media (max-width: 991.98px) {
        .module-card-header {
            padding: 1rem;
        }
        .module-header-left {
            align-items: flex-start;
        }
        .module-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            font-size: 1.15rem;
        }
        .module-header-title {
            font-size: 1.15rem;
        }
        .module-header-sub {
            font-size: .8rem;
        }
        .vs-panel-head {
            padding-left: 14px;
            padding-right: 14px;
        }
        .vehicle-list-table thead th,
        .vehicle-list-table tbody td {
            font-size: .86rem;
        }
        .manutencao-card__header,
        .manutencao-card .card-body {
            padding-left: 14px;
            padding-right: 14px;
        }
    }
</style>
@endsection
