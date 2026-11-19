@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Refeições servidas em cada unidade" :create-url="auth()->user()->hasPermissao('cardapio.create') ? route('cardapio.create') : null" :create-can="'cardapio.create'" />
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
        @include('partials.ux-view-toggle', ['viewKey' => 'cardapio_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
                <div class="ux-cards">
            @forelse($cardapios as $cardapio)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar"><i class="fas fa-utensils"></i></div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $cardapio->data->format('d/m/Y') }}</div>
                            <div class="ux-card__sub">{{ $cardapio->data->locale('pt_BR')->translatedFormat('l') }}</div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="badge badge-info">{{ $cardapio->tipo_label }}</span>
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Escola</span>
                            <span class="ux-card__meta-value">{{ $cardapio->escola->nome ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Cardápio</span>
                            <span class="ux-card__meta-value"><small>{{ \Illuminate\Support\Str::limit($cardapio->descricao, 120) }}</small></span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Restrições</span>
                            <span class="ux-card__meta-value">@if($cardapio->restricoes_alimentares)
                                    <small class="text-warning">{{ \Illuminate\Support\Str::limit($cardapio->restricoes_alimentares, 60) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Status</span>
                            <span class="ux-card__meta-value">@if($cardapio->esta_ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        @can('cardapio.edit')
                                    <a href="{{ route('cardapio.edit', $cardapio->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('cardapio.destroy')
                                    <x-delete-button :action="route('cardapio.destroy', $cardapio->id)" titulo="Excluir" mensagem="Excluir o cardápio de {{ $cardapio->tipo_label }} em {{ $cardapio->data->format('d/m/Y') }}?" />
                                @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-utensils fa-2x mb-2 d-block"></i>
                    Nenhum cardápio cadastrado.
                </div>
            @endforelse
        </div>
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Data</th>
                        <th>Dia</th>
                        <th>Escola</th>
                        <th>Refeição</th>
                        <th>Cardápio</th>
                        <th>Restrições</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cardapios as $cardapio)
                        <tr>
                            <td class="font-weight-bold text-nowrap">{{ $cardapio->data->format('d/m/Y') }}</td>
                            <td>{{ $cardapio->data->locale('pt_BR')->translatedFormat('l') }}</td>
                            <td>{{ $cardapio->escola->nome ?? '—' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $cardapio->tipo_label }}</span>
                            </td>
                            <td style="max-width:320px;">
                                <small>{{ \Illuminate\Support\Str::limit($cardapio->descricao, 120) }}</small>
                            </td>
                            <td>
                                @if($cardapio->restricoes_alimentares)
                                    <small class="text-warning">{{ \Illuminate\Support\Str::limit($cardapio->restricoes_alimentares, 60) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($cardapio->esta_ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('cardapio.edit')
                                    <a href="{{ route('cardapio.edit', $cardapio->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('cardapio.destroy')
                                    <x-delete-button :action="route('cardapio.destroy', $cardapio->id)" titulo="Excluir" mensagem="Excluir o cardápio de {{ $cardapio->tipo_label }} em {{ $cardapio->data->format('d/m/Y') }}?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-utensils fa-2x mb-2 d-block"></i>
                                Nenhum cardápio cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($cardapios->hasPages())
        <div class="card-footer">
            {{ $cardapios->links() }}
        </div>
    @endif
</div>
@stop
