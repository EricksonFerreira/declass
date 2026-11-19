@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Tabelas de mensalidade por plano" :create-url="auth()->user()->hasPermissao('plano_mensalidade.create') ? route('plano_mensalidade.create') : null" :create-can="'plano_mensalidade.create'" />
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
        @include('partials.ux-view-toggle', ['viewKey' => 'plano_mensalidade_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
                <div class="ux-cards">
            @forelse($planos as $plano)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar"><i class="fas fa-hand-holding-usd"></i></div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $plano->nome }}</div>
                            <div class="ux-card__sub">R$ {{ number_format($plano->valor, 2, ',', '.') }}</div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="badge badge-{{ $plano->ativo === 'S' ? 'success' : 'secondary' }}">
                                    {{ $plano->ativo === 'S' ? 'Ativo' : 'Inativo' }}
                                </span>
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Descrição</span>
                            <span class="ux-card__meta-value">{{ \Illuminate\Support\Str::limit($plano->descricao, 80) ?: '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Matrículas</span>
                            <span class="ux-card__meta-value">{{ $plano->matriculas_count ?? $plano->matriculas->count() }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        @can('plano_mensalidade.edit')
                                    <a href="{{ route('plano_mensalidade.edit', $plano->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('plano_mensalidade.destroy')
                                    <x-delete-button :action="route('plano_mensalidade.destroy', $plano->id)" titulo="Excluir" mensagem="Excluir o plano &quot;{{ $plano->nome }}&quot;?" />
                                @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-hand-holding-usd fa-2x mb-2 d-block"></i>
                    Nenhum plano de mensalidade encontrado.
                </div>
            @endforelse
        </div>
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nome</th>
                        <th class="text-right">Valor mensal</th>
                        <th>Descrição</th>
                        <th>Matrículas</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planos as $plano)
                        <tr>
                            <td class="font-weight-bold">{{ $plano->nome }}</td>
                            <td class="text-right">R$ {{ number_format($plano->valor, 2, ',', '.') }}</td>
                            <td class="text-muted" style="font-size:.8rem; max-width:280px;">{{ \Illuminate\Support\Str::limit($plano->descricao, 80) ?: '—' }}</td>
                            <td>{{ $plano->matriculas_count ?? $plano->matriculas->count() }}</td>
                            <td>
                                <span class="badge badge-{{ $plano->ativo === 'S' ? 'success' : 'secondary' }}">
                                    {{ $plano->ativo === 'S' ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                @can('plano_mensalidade.edit')
                                    <a href="{{ route('plano_mensalidade.edit', $plano->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('plano_mensalidade.destroy')
                                    <x-delete-button :action="route('plano_mensalidade.destroy', $plano->id)" titulo="Excluir" mensagem="Excluir o plano &quot;{{ $plano->nome }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-hand-holding-usd fa-2x mb-2 d-block"></i>
                                Nenhum plano de mensalidade encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($planos->hasPages())
        <div class="card-footer">
            {{ $planos->links() }}
        </div>
    @endif
</div>
@stop
