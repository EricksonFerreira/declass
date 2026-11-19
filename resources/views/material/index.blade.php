@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Materiais, insumos e controle de estoque por unidade" :create-url="auth()->user()->hasPermissao('material.create') ? route('material.create') : null" :create-can="'material.create'" />
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
        @include('partials.ux-view-toggle', ['viewKey' => 'material_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
                <div class="ux-cards">
            @forelse($materiais as $material)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar"><i class="fas fa-boxes"></i></div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title"><strong>{{ $material->nome }}</strong>
                                @if($material->ativo !== 'S')
                                    <span class="badge badge-secondary ml-1">Inativo</span>
                                @endif</div>
                            <div class="ux-card__sub">{{ $material->escola->nome ?? 'Rede toda' }}</div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="badge badge-light border">{{ $material->categoria_label }}</span>
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Estoque atual</span>
                            <span class="ux-card__meta-value"><strong>{{ $material->estoque_formatado }}</strong>
                                @if($material->baixo_estoque)
                                    <br><span class="badge badge-danger">Estoque baixo</span>
                                @endif</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Mínimo</span>
                            <span class="ux-card__meta-value">{{ number_format((float) $material->estoque_minimo, 0, ',', '.') }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Localização</span>
                            <span class="ux-card__meta-value"><small>{{ $material->localizacao ?? '—' }}</small></span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        <a href="{{ route('material.movimentacoes', $material->id) }}" class="btn btn-xs btn-info" title="Movimentações"><i class="fa fa-exchange-alt"></i></a>
                                @can('material.edit')
                                    <a href="{{ route('material.edit', $material->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('material.destroy')
                                    <x-delete-button :action="route('material.destroy', $material->id)" titulo="Excluir" mensagem="Excluir o material &quot;{{ $material->nome }}&quot;? (só é possível sem movimentações)" />
                                @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-boxes fa-2x mb-2 d-block"></i>
                    Nenhum material cadastrado.
                </div>
            @endforelse
        </div>
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Material</th>
                        <th>Categoria</th>
                        <th>Escola</th>
                        <th>Estoque atual</th>
                        <th>Mínimo</th>
                        <th>Localização</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materiais as $material)
                        <tr class="{{ $material->baixo_estoque ? 'table-warning' : '' }}">
                            <td>
                                <strong>{{ $material->nome }}</strong>
                                @if($material->ativo !== 'S')
                                    <span class="badge badge-secondary ml-1">Inativo</span>
                                @endif
                            </td>
                            <td><span class="badge badge-light border">{{ $material->categoria_label }}</span></td>
                            <td>{{ $material->escola->nome ?? 'Rede toda' }}</td>
                            <td>
                                <strong>{{ $material->estoque_formatado }}</strong>
                                @if($material->baixo_estoque)
                                    <br><span class="badge badge-danger">Estoque baixo</span>
                                @endif
                            </td>
                            <td>{{ number_format((float) $material->estoque_minimo, 0, ',', '.') }}</td>
                            <td><small>{{ $material->localizacao ?? '—' }}</small></td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('material.movimentacoes', $material->id) }}" class="btn btn-xs btn-info" title="Movimentações"><i class="fa fa-exchange-alt"></i></a>
                                @can('material.edit')
                                    <a href="{{ route('material.edit', $material->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('material.destroy')
                                    <x-delete-button :action="route('material.destroy', $material->id)" titulo="Excluir" mensagem="Excluir o material &quot;{{ $material->nome }}&quot;? (só é possível sem movimentações)" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-boxes fa-2x mb-2 d-block"></i>
                                Nenhum material cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($materiais->hasPages())
        <div class="card-footer">
            {{ $materiais->links() }}
        </div>
    @endif
</div>
@stop
