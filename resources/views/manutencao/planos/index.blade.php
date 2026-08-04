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
        <div class="vs-panel shadow-sm border-0 mb-3">
            <div class="vs-panel-head">
                <div>
                    <span class="vs-panel-head__eyebrow">Veículos · Manutenção preventiva</span>
                    <h4 class="vs-panel-head__title mb-1">Planos de manutenção</h4>
                    <p class="vs-panel-head__sub mb-0">Configure, revise e compare planos com vínculo por veículo ou por grupo de frota.</p>
                </div>
                <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                    <a href="{{ route('manutencao.planos.create') }}" class="btn-xp btn-xp--sm">
                        <i class="fas fa-plus mr-1"></i> Novo plano
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

@section('css')
<style>
    .manutencao-preventiva-shell { padding-bottom: 8px; }
    .config-card {
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }
    .vs-panel-head {
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        padding: 14px 16px;
        border-bottom: 1px solid #e7edf6;
    }
    .vehicle-table-wrap {
        max-height: none;
        overflow: auto;
    }
    .vehicle-list-table {
        min-width: 940px;
        margin-bottom: 0;
    }
    .vehicle-list-table thead th {
        white-space: nowrap;
        background: #fff;
        font-weight: 800;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
        text-transform: uppercase;
        font-size: .72rem;
        letter-spacing: .06em;
    }
    .vehicle-list-table tbody td { vertical-align: middle; }
    .vehicle-list-table tbody tr:hover { background: #f8fafc; }
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
    @media (max-width: 991.98px) {
        .vs-panel-head,
        .doc-filters { padding-left: 14px; padding-right: 14px; }
        .vehicle-list-table thead th,
        .vehicle-list-table tbody td { font-size: .86rem; }
    }
</style>
@endsection
