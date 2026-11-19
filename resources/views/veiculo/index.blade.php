@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Frota de transporte das unidades" :create-url="auth()->user()->hasPermissao('veiculo.create') ? route('veiculo.create') : null" :create-can="'veiculo.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @include('partials.ux-view-toggle', ['viewKey' => 'veiculo_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
                <div class="ux-cards">
            @forelse($veiculos as $veiculo)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar"><i class="fas fa-bus"></i></div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $veiculo->placa }}</div>
                            <div class="ux-card__sub">{{ $veiculo->modelo }}</div>
                        </div>
                        <div class="ux-card__badges">
                            @if($veiculo->esta_ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Ano</span>
                            <span class="ux-card__meta-value">{{ $veiculo->ano ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Capacidade</span>
                            <span class="ux-card__meta-value">{{ $veiculo->capacidade ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Motorista</span>
                            <span class="ux-card__meta-value">{{ $veiculo->motorista_nome ?? '—' }}
                                @if($veiculo->motorista_telefone)
                                    <br><small class="text-muted">{{ $veiculo->motorista_telefone }}</small>
                                @endif</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Escola</span>
                            <span class="ux-card__meta-value">{{ $veiculo->escola->nome ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Passageiros</span>
                            <span class="ux-card__meta-value">{{ $veiculo->passageiros }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        @can('veiculo.edit')
                                    <a href="{{ route('veiculo.edit', $veiculo->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('veiculo.destroy')
                                    <x-delete-button :action="route('veiculo.destroy', $veiculo->id)" titulo="Excluir" mensagem="Excluir o veículo &quot;{{ $veiculo->placa }}&quot;?" />
                                @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-bus fa-2x mb-2 d-block"></i>
                    Nenhum veículo cadastrado.
                </div>
            @endforelse
        </div>
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Placa</th>
                        <th>Modelo</th>
                        <th>Ano</th>
                        <th>Capacidade</th>
                        <th>Motorista</th>
                        <th>Escola</th>
                        <th>Passageiros</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($veiculos as $veiculo)
                        <tr>
                            <td class="font-weight-bold">{{ $veiculo->placa }}</td>
                            <td>{{ $veiculo->modelo }}</td>
                            <td>{{ $veiculo->ano ?? '—' }}</td>
                            <td>{{ $veiculo->capacidade ?? '—' }}</td>
                            <td>
                                {{ $veiculo->motorista_nome ?? '—' }}
                                @if($veiculo->motorista_telefone)
                                    <br><small class="text-muted">{{ $veiculo->motorista_telefone }}</small>
                                @endif
                            </td>
                            <td>{{ $veiculo->escola->nome ?? '—' }}</td>
                            <td class="text-center">{{ $veiculo->passageiros }}</td>
                            <td>
                                @if($veiculo->esta_ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('veiculo.edit')
                                    <a href="{{ route('veiculo.edit', $veiculo->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('veiculo.destroy')
                                    <x-delete-button :action="route('veiculo.destroy', $veiculo->id)" titulo="Excluir" mensagem="Excluir o veículo &quot;{{ $veiculo->placa }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-bus fa-2x mb-2 d-block"></i>
                                Nenhum veículo cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($veiculos->hasPages())
        <div class="card-footer">
            {{ $veiculos->links() }}
        </div>
    @endif
</div>
@stop
