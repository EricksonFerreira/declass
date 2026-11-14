@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Turmas por escola, ano letivo e etapa" :create-url="auth()->user()->hasPermissao('turma.create') ? route('turma.create') : null" :create-can="'turma.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .turmas-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 10px; padding: 8px 6px 12px; }
        .turmas-cards .ux-card { border-radius: 12px; box-shadow: 0 1px 4px rgba(11,18,32,.05); }
        .turmas-cards .ux-card:hover { transform: none; box-shadow: 0 4px 14px rgba(11,18,32,.10); }
        .turmas-cards .ux-card__accent { display: none; }
        .turmas-cards .ux-card__head { padding: 9px 11px 7px; gap: 8px; }
        .turmas-cards .ux-card__avatar { width: 32px; height: 32px; border-radius: 8px; font-size: .68rem; }
        .turmas-cards .ux-card__title { font-size: .8rem; white-space: normal; }
        .turmas-cards .ux-card__sub { font-size: .68rem; }
        .turmas-cards .ux-card__badges { flex-direction: row; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .turmas-cards .ux-card__badge { font-size: .6rem; padding: 3px 6px; }
        .turmas-cards .ux-card__meta { padding: 7px 11px 5px; gap: 5px 10px; }
        .turmas-cards .ux-card__meta-label { font-size: .6rem; }
        .turmas-cards .ux-card__meta-value { font-size: .74rem; }
        .turmas-cards .ux-card__foot { padding: 7px 11px 9px; }
        .turmas-cards .ux-card__foot .btn { padding: .15rem .45rem; font-size: .72rem; }
        .turmas-cards .ux-card--muted { opacity: .62; }
        .turmas-cards .ux-card--muted:hover { opacity: .9; }
        .turmas-cards .ux-card__title a { color: inherit; }
    </style>
@endpush

@section('content')
@php
    $anoBadgeCard = fn ($status) => match ($status) {
        'encerrado' => 'ux-card__badge--red',
        'planejamento' => 'ux-card__badge--blue',
        default => 'ux-card__badge--green',
    };
@endphp
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @include('partials.ux-view-toggle', ['viewKey' => 'turma_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards turmas-cards">
            @forelse($turmas as $turma)
                @php
                    $anoStatus = $turma->anoLetivo?->status ?? 'letivo';
                    $partes = preg_split('/\s+/', trim((string) $turma->nome)) ?: [];
                    $iniciais = collect($partes)->filter()->take(2)
                        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'T';
                @endphp
                <article class="ux-card {{ $anoStatus === 'letivo' ? '' : 'ux-card--muted' }}">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">{{ $iniciais }}</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">
                                <a href="{{ route('turma.show', $turma->id) }}">{{ $turma->nome }}</a>
                            </div>
                            <div class="ux-card__sub">{{ $turma->escola->nome ?? '—' }}</div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="ux-card__badge {{ $anoBadgeCard($anoStatus) }}"
                                title="Ano letivo {{ $turma->anoLetivo->ano ?? '' }} — {{ \App\Models\AnoLetivo::STATUS[$anoStatus] ?? $anoStatus }}">
                                {{ \App\Models\AnoLetivo::STATUS[$anoStatus] ?? $anoStatus }}
                            </span>
                            @unless($turma->esta_ativa)
                                <span class="ux-card__badge ux-card__badge--gray">Inativa</span>
                            @endunless
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Ano letivo</span>
                            <span class="ux-card__meta-value">{{ $turma->anoLetivo->ano ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Etapa</span>
                            <span class="ux-card__meta-value">{{ $turma->etapa->nome ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Turno</span>
                            <span class="ux-card__meta-value">{{ \App\Models\Turma::TURNOS[$turma->turno ?? ''] ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Sala</span>
                            <span class="ux-card__meta-value">{{ $turma->sala ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        <a href="{{ route('turma.show', $turma->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fas fa-eye"></i></a>
                        @can('turma.grade')
                            <a href="{{ route('turma.grade', $turma->id) }}" class="btn btn-xs btn-outline-info" title="Grade horária"><i class="fa fa-calendar-week"></i></a>
                        @endcan
                        @can('turma.edit')
                            <a href="{{ route('turma.edit', $turma->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('turma.destroy')
                            <x-delete-button :action="route('turma.destroy', $turma->id)" titulo="Excluir" mensagem="Excluir a turma &quot;{{ $turma->nome }}&quot;? Disciplinas e atribuições vinculadas são removidas junto." />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-chalkboard-teacher fa-2x mb-2 d-block"></i>
                    Nenhuma turma encontrada.
                </div>
            @endforelse
        </div>
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Turma</th>
                        <th>Escola</th>
                        <th>Ano letivo</th>
                        <th>Etapa</th>
                        <th>Turno</th>
                        <th>Sala</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($turmas as $turma)
                        @php $anoStatus = $turma->anoLetivo?->status ?? 'letivo'; @endphp
                        <tr>
                            <td>
                                <a href="{{ route('turma.show', $turma->id) }}" class="font-weight-bold">{{ $turma->nome }}</a>
                            </td>
                            <td>{{ $turma->escola->nome ?? '—' }}</td>
                            <td>
                                {{ $turma->anoLetivo->ano ?? '—' }}
                                <span class="badge badge-{{ \App\Models\AnoLetivo::STATUS_BADGES[$anoStatus] ?? 'secondary' }}"
                                    title="Situação do ano letivo">{{ \App\Models\AnoLetivo::STATUS[$anoStatus] ?? $anoStatus }}</span>
                            </td>
                            <td>{{ $turma->etapa->nome ?? '—' }}</td>
                            <td>{{ \App\Models\Turma::TURNOS[$turma->turno ?? ''] ?? '—' }}</td>
                            <td>{{ $turma->sala ?? '—' }}</td>
                            <td>
                                @if($turma->esta_ativa)
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-secondary">Inativa</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('turma.show', $turma->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                                @can('turma.grade')
                                    <a href="{{ route('turma.grade', $turma->id) }}" class="btn btn-xs btn-outline-info" title="Grade horária"><i class="fa fa-calendar-week"></i></a>
                                @endcan
                                @can('turma.edit')
                                    <a href="{{ route('turma.edit', $turma->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('turma.destroy')
                                    <x-delete-button :action="route('turma.destroy', $turma->id)" titulo="Excluir" mensagem="Excluir a turma &quot;{{ $turma->nome }}&quot;? Disciplinas e atribuições vinculadas são removidas junto." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-chalkboard-teacher fa-2x mb-2 d-block"></i>
                                Nenhuma turma encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($turmas->hasPages())
        <div class="card-footer">
            {{ $turmas->links() }}
        </div>
    @endif
</div>
@stop
