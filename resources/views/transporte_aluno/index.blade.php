@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Alunos vinculados às rotas de transporte" :create-url="auth()->user()->hasPermissao('transporte_aluno.create') ? route('transporte_aluno.create') : null" :create-can="'transporte_aluno.create'" />
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
        @include('partials.ux-view-toggle', ['viewKey' => 'transporte_aluno_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
                <div class="ux-cards">
            @forelse($passageiros as $p)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar"><i class="fas fa-user-friends"></i></div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $p->aluno->pessoa->nome ?? '—' }}</div>
                            <div class="ux-card__sub">{{ $p->aluno->registro_academico ?? '—' }}</div>
                        </div>
                        <div class="ux-card__badges">
                            @if($p->esta_ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Rota</span>
                            <span class="ux-card__meta-value">{{ $p->rota->nome ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Escola</span>
                            <span class="ux-card__meta-value">{{ $p->rota->escola->nome ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Veículo</span>
                            <span class="ux-card__meta-value">{{ $p->rota->veiculo->placa ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Parada</span>
                            <span class="ux-card__meta-value">{{ $p->parada->nome ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        @can('transporte_aluno.edit')
                                    <a href="{{ route('transporte_aluno.edit', $p->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('transporte_aluno.destroy')
                                    <x-delete-button :action="route('transporte_aluno.destroy', $p->id)" titulo="Excluir" mensagem="Remover {{ $p->aluno->pessoa->nome ?? 'o aluno' }} da rota &quot;{{ $p->rota->nome ?? '' }}&quot;?" />
                                @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-user-friends fa-2x mb-2 d-block"></i>
                    Nenhum passageiro vinculado.
                </div>
            @endforelse
        </div>
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Aluno</th>
                        <th>RA</th>
                        <th>Rota</th>
                        <th>Escola</th>
                        <th>Veículo</th>
                        <th>Parada</th>
                        <th>Situação</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($passageiros as $p)
                        <tr>
                            <td class="font-weight-bold">{{ $p->aluno->pessoa->nome ?? '—' }}</td>
                            <td>{{ $p->aluno->registro_academico ?? '—' }}</td>
                            <td>{{ $p->rota->nome ?? '—' }}</td>
                            <td>{{ $p->rota->escola->nome ?? '—' }}</td>
                            <td>{{ $p->rota->veiculo->placa ?? '—' }}</td>
                            <td>{{ $p->parada->nome ?? '—' }}</td>
                            <td>
                                @if($p->esta_ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('transporte_aluno.edit')
                                    <a href="{{ route('transporte_aluno.edit', $p->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('transporte_aluno.destroy')
                                    <x-delete-button :action="route('transporte_aluno.destroy', $p->id)" titulo="Excluir" mensagem="Remover {{ $p->aluno->pessoa->nome ?? 'o aluno' }} da rota &quot;{{ $p->rota->nome ?? '' }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-user-friends fa-2x mb-2 d-block"></i>
                                Nenhum passageiro vinculado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($passageiros->hasPages())
        <div class="card-footer">
            {{ $passageiros->links() }}
        </div>
    @endif
</div>
@stop
