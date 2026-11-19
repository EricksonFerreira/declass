@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Unidades e escolas do tenant" :create-url="auth()->user()->hasPermissao('escola.create') ? route('escola.create') : null" :create-can="'escola.create'" />
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
        @include('partials.ux-view-toggle', ['viewKey' => 'escola_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
                <div class="ux-cards">
            @forelse($escolas as $escola)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar"><i class="fas fa-school"></i></div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title"><a href="{{ route('escola.show', $escola->id) }}" class="font-weight-bold">{{ $escola->nome }}</a>
                                <div class="text-muted" style="font-size:.75rem;">{{ $escola->slug }}</div></div>
                            <div class="ux-card__sub">{{ trim(($escola->cidade ?? '').($escola->estado ? '/'.$escola->estado : '')) ?: '—' }}</div>
                        </div>
                        <div class="ux-card__badges">
                            @if($escola->esta_ativa)
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-secondary">Inativa</span>
                                @endif
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Capacidade</span>
                            <span class="ux-card__meta-value">{{ $escola->capacidade_alunos ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        <a href="{{ route('escola.show', $escola->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                                @can('escola.edit')
                                    <a href="{{ route('escola.edit', $escola->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('escola.destroy')
                                    <x-delete-button :action="route('escola.destroy', $escola->id)" titulo="Excluir" mensagem="Excluir a escola &quot;{{ $escola->nome }}&quot;? Os dados vinculados são preservados." />
                                @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-school fa-2x mb-2 d-block"></i>
                    Nenhuma escola encontrada.
                </div>
            @endforelse
        </div>
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nome</th>
                        <th>Cidade/UF</th>
                        <th>Capacidade</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($escolas as $escola)
                        <tr>
                            <td>
                                <a href="{{ route('escola.show', $escola->id) }}" class="font-weight-bold">{{ $escola->nome }}</a>
                                <div class="text-muted" style="font-size:.75rem;">{{ $escola->slug }}</div>
                            </td>
                            <td>{{ trim(($escola->cidade ?? '').($escola->estado ? '/'.$escola->estado : '')) ?: '—' }}</td>
                            <td>{{ $escola->capacidade_alunos ?? '—' }}</td>
                            <td>
                                @if($escola->esta_ativa)
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-secondary">Inativa</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('escola.show', $escola->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                                @can('escola.edit')
                                    <a href="{{ route('escola.edit', $escola->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('escola.destroy')
                                    <x-delete-button :action="route('escola.destroy', $escola->id)" titulo="Excluir" mensagem="Excluir a escola &quot;{{ $escola->nome }}&quot;? Os dados vinculados são preservados." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-school fa-2x mb-2 d-block"></i>
                                Nenhuma escola encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($escolas->hasPages())
        <div class="card-footer">
            {{ $escolas->links() }}
        </div>
    @endif
</div>
@stop
