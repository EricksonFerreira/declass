@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Alunos do tenant" :create-url="auth()->user()->hasPermissao('aluno.create') ? route('aluno.create') : null" :create-can="'aluno.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .alu-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .alu-cards .ux-card__avatar img { width:100%; height:100%; border-radius:50%; object-fit:cover; display:block; }
    </style>
@endpush

@section('content')
@php
    $iniciais = function (string $nome): string {
        $partes = preg_split('/\s+/', trim($nome)) ?: [];
        return collect($partes)->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: '?';
    };
@endphp
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @include('partials.ux-view-toggle', ['viewKey' => 'aluno_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards alu-cards">
            @forelse($alunos as $aluno)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">
                            @if($aluno->foto_url)
                                <img src="{{ $aluno->foto_url }}" alt="{{ $aluno->pessoa->nome_exibicao ?? '' }}">
                            @else
                                {{ $iniciais($aluno->pessoa->nome_exibicao ?? '?') }}
                            @endif
                        </div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__id">RA {{ $aluno->registro_academico ?? '—' }}</div>
                            <div class="ux-card__title">{{ $aluno->pessoa->nome_exibicao ?? '—' }}</div>
                            <div class="ux-card__sub">{{ $aluno->escola->nome ?? '—' }}</div>
                        </div>
                        <div class="ux-card__badges">
                            @if($aluno->esta_ativo)
                                <span class="ux-card__badge ux-card__badge--green">Ativo</span>
                            @else
                                <span class="ux-card__badge ux-card__badge--gray">Inativo</span>
                            @endif
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        @if($aluno->pessoa?->nome_social)
                            <div class="ux-card__meta-row ux-card__meta-row--full">
                                <span class="ux-card__meta-label">Nome civil</span>
                                <span class="ux-card__meta-value">{{ $aluno->pessoa->nome }}</span>
                            </div>
                        @endif
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Ingresso</span>
                            <span class="ux-card__meta-value">{{ optional($aluno->data_ingresso)->format('d/m/Y') ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        <a href="{{ route('aluno.show', $aluno->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                        <a href="{{ route('aluno.historico', $aluno->id) }}" class="btn btn-xs btn-dark" title="Histórico completo"><i class="fa fa-history"></i></a>
                        @can('aluno.edit')
                            <a href="{{ route('aluno.edit', $aluno->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('aluno.destroy')
                            <x-delete-button :action="route('aluno.destroy', $aluno->id)" titulo="Excluir" mensagem="Excluir o aluno &quot;{{ $aluno->pessoa->nome_exibicao ?? '' }}&quot;? Os dados são preservados (exclusão lógica)." />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-user-graduate fa-2x mb-2 d-block"></i>
                    Nenhum aluno encontrado.
                </div>
            @endforelse
        </div>

        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nome</th>
                        <th>RA</th>
                        <th>Escola</th>
                        <th>Data de ingresso</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alunos as $aluno)
                        <tr>
                            <td>
                                <a href="{{ route('aluno.show', $aluno->id) }}" class="font-weight-bold">{{ $aluno->pessoa->nome_exibicao ?? '—' }}</a>
                                @if($aluno->pessoa?->nome_social)
                                    <div class="text-muted" style="font-size:.75rem;">{{ $aluno->pessoa->nome }}</div>
                                @endif
                            </td>
                            <td>{{ $aluno->registro_academico ?? '—' }}</td>
                            <td>{{ $aluno->escola->nome ?? '—' }}</td>
                            <td>{{ optional($aluno->data_ingresso)->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if($aluno->esta_ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('aluno.show', $aluno->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                                <a href="{{ route('aluno.historico', $aluno->id) }}" class="btn btn-xs btn-dark" title="Histórico completo"><i class="fa fa-history"></i></a>
                                @can('aluno.edit')
                                    <a href="{{ route('aluno.edit', $aluno->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('aluno.destroy')
                                    <x-delete-button :action="route('aluno.destroy', $aluno->id)" titulo="Excluir" mensagem="Excluir o aluno &quot;{{ $aluno->pessoa->nome_exibicao ?? '' }}&quot;? Os dados são preservados (exclusão lógica)." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-user-graduate fa-2x mb-2 d-block"></i>
                                Nenhum aluno encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($alunos->hasPages())
        <div class="card-footer">
            {{ $alunos->links() }}
        </div>
    @endif
</div>
@stop
