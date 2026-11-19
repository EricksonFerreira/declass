@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Inscrições de alunos em turmas" :create-url="auth()->user()->hasPermissao('matricula.create') ? route('matricula.create') : null" :create-can="'matricula.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .mat-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .mat-cards .ux-card__avatar img { width:100%; height:100%; border-radius:50%; object-fit:cover; display:block; }
    </style>
@endpush

@section('content')
@php
    $statusCores = ['ativa' => 'success', 'trancada' => 'warning', 'cancelada' => 'danger', 'concluida' => 'info'];
    $statusBadge = ['ativa' => 'ux-card__badge--green', 'trancada' => 'ux-card__badge--amber', 'cancelada' => 'ux-card__badge--red', 'concluida' => 'ux-card__badge--blue'];
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
        @include('partials.ux-view-toggle', ['viewKey' => 'matricula_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards mat-cards">
            @forelse($matriculas as $matricula)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">
                            @if($matricula->aluno?->foto_url)
                                <img src="{{ $matricula->aluno->foto_url }}" alt="{{ $matricula->aluno->pessoa->nome ?? '' }}">
                            @else
                                {{ $iniciais($matricula->aluno->pessoa->nome ?? '?') }}
                            @endif
                        </div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__id">Nº {{ $matricula->numero_chamada ?? '—' }}@if($matricula->aluno?->ra) · RA {{ $matricula->aluno->ra }}@endif</div>
                            <div class="ux-card__title">{{ $matricula->aluno->pessoa->nome ?? '—' }}</div>
                            <div class="ux-card__sub">{{ $matricula->turma->nome ?? '—' }} · {{ $matricula->turma->escola->nome ?? '—' }}</div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="ux-card__badge {{ $statusBadge[$matricula->status] ?? 'ux-card__badge--gray' }}">{{ $matricula->status_label }}</span>
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Ano letivo</span>
                            <span class="ux-card__meta-value">{{ $matricula->anoLetivo->ano ?? ($matricula->turma->anoLetivo->ano ?? '—') }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Matriculado em</span>
                            <span class="ux-card__meta-value">{{ $matricula->data_matricula?->format('d/m/Y') ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        @can('matricula.edit')
                            <a href="{{ route('matricula.edit', $matricula->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('matricula.destroy')
                            <x-delete-button :action="route('matricula.destroy', $matricula->id)" titulo="Excluir" mensagem="Excluir a matrícula de &quot;{{ $matricula->aluno->pessoa->nome ?? 'aluno' }}&quot; nesta turma?" />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-user-graduate fa-2x mb-2 d-block"></i>
                    Nenhuma matrícula encontrada.
                </div>
            @endforelse
        </div>

        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nº</th>
                        <th>Aluno</th>
                        <th>Turma</th>
                        <th>Escola</th>
                        <th>Ano letivo</th>
                        <th>Matriculado em</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matriculas as $matricula)
                        <tr>
                            <td class="text-muted">{{ $matricula->numero_chamada ?? '—' }}</td>
                            <td>
                                <span class="font-weight-bold">{{ $matricula->aluno->pessoa->nome ?? '—' }}</span>
                                @if($matricula->aluno?->ra)
                                    <small class="text-muted d-block">RA {{ $matricula->aluno->ra }}</small>
                                @endif
                            </td>
                            <td>{{ $matricula->turma->nome ?? '—' }}</td>
                            <td>{{ $matricula->turma->escola->nome ?? '—' }}</td>
                            <td>{{ $matricula->anoLetivo->ano ?? ($matricula->turma->anoLetivo->ano ?? '—') }}</td>
                            <td>{{ $matricula->data_matricula?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $statusCores[$matricula->status] ?? 'secondary' }}">{{ $matricula->status_label }}</span>
                            </td>
                            <td class="text-right">
                                @can('matricula.edit')
                                    <a href="{{ route('matricula.edit', $matricula->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('matricula.destroy')
                                    <x-delete-button :action="route('matricula.destroy', $matricula->id)" titulo="Excluir" mensagem="Excluir a matrícula de &quot;{{ $matricula->aluno->pessoa->nome ?? 'aluno' }}&quot; nesta turma?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-user-graduate fa-2x mb-2 d-block"></i>
                                Nenhuma matrícula encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($matriculas->hasPages())
        <div class="card-footer">
            {{ $matriculas->links() }}
        </div>
    @endif
</div>
@stop
