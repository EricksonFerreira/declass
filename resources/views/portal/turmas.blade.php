@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :subtitle="$isAluno ? 'Turmas em que você está matriculado' : 'Turmas em que seus alunos estão matriculados'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .turmas-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(225px, 1fr)); gap: 10px; padding: 8px 6px 12px; }
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
        .turmas-cards .ux-card__foot .btn { padding: .15rem .5rem; font-size: .72rem; }
        .turmas-cards .ux-card--muted { opacity: .62; }
        .turmas-cards .ux-card--muted:hover { opacity: .85; }
        .turmas-cards .ux-card__title a { color: inherit; }
    </style>
@endpush

@section('content')
<x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />

<div class="card">
    <div class="card-header py-2">
        <h3 class="card-title text-sm font-weight-bold">Turmas <span class="badge badge-primary">{{ $matriculas->count() }}</span></h3>
        <div class="card-tools">
            @include('partials.ux-view-toggle', ['viewKey' => 'portal_turmas_view'])
        </div>
    </div>
    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards turmas-cards">
            @forelse($matriculas as $matricula)
                @php
                    $turma = $matricula->turma;
                    $anoStatus = $turma->anoLetivo?->status ?? 'letivo';
                    $anoEmCurso = $anoStatus === 'letivo';
                    $resultado = $resultados[$matricula->id] ?? null;
                    $partes = preg_split('/\s+/', trim((string) $turma->nome)) ?: [];
                    $iniciais = collect($partes)->filter()->take(2)
                        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'T';
                @endphp
                <article class="ux-card {{ $anoEmCurso ? '' : 'ux-card--muted' }}">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">{{ $iniciais }}</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">
                                <a href="{{ route('portal.turma', $turma->id) }}">{{ $turma->nome }}</a>
                            </div>
                            <div class="ux-card__sub">
                                {{ $turma->escola->nome ?? '—' }}@unless($isAluno) · {{ $matricula->aluno->pessoa->nome ?? $matricula->aluno->nome }}@endunless
                            </div>
                        </div>
                        <div class="ux-card__badges">
                            @if(!$anoEmCurso)
                                <span class="ux-card__badge ux-card__badge--gray" title="Ano letivo {{ \App\Models\AnoLetivo::STATUS[$anoStatus] ?? $anoStatus }}">{{ \App\Models\AnoLetivo::STATUS[$anoStatus] ?? 'Encerrado' }}</span>
                            @endif
                            @if($resultado === 'aprovado')
                                <span class="ux-card__badge ux-card__badge--green" title="Média em todas as disciplinas igual ou acima do corte">Aprovado</span>
                            @elseif($resultado === 'reprovado')
                                <span class="ux-card__badge ux-card__badge--red" title="Média abaixo do corte em alguma disciplina">Reprovado</span>
                            @endif
                            @unless($turma->estaAtiva())
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
                        <a href="{{ route('portal.turma', $turma->id) }}" class="btn btn-xs btn-info">
                            <i class="fas fa-eye mr-1"></i>Ver
                        </a>
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-chalkboard-teacher fa-2x mb-2 d-block"></i>
                    Nenhuma matrícula encontrada.
                </div>
            @endforelse
        </div>
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Turma</th>
                        @unless($isAluno)<th>Aluno</th>@endunless
                        <th>Escola</th>
                        <th>Ano letivo</th>
                        <th>Etapa</th>
                        <th>Turno</th>
                        <th>Sala</th>
                        <th>Status</th>
                        <th>Resultado</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matriculas as $matricula)
                        @php
                            $turma = $matricula->turma;
                            $anoEmCurso = ($turma->anoLetivo?->status ?? '') === 'letivo';
                        @endphp
                        <tr class="{{ $anoEmCurso ? '' : 'table-secondary text-muted' }}">
                            <td>
                                <a href="{{ route('portal.turma', $turma->id) }}" class="font-weight-bold {{ $anoEmCurso ? '' : 'text-muted' }}">{{ $turma->nome }}</a>
                            </td>
                            @unless($isAluno)
                                <td>{{ $matricula->aluno->pessoa->nome ?? $matricula->aluno->nome }}</td>
                            @endunless
                            <td>{{ $turma->escola->nome ?? '—' }}</td>
                            <td>
                                {{ $turma->anoLetivo->ano ?? '—' }}
                                @unless($anoEmCurso)
                                    <span class="badge badge-dark">{{ \App\Models\AnoLetivo::STATUS[$turma->anoLetivo?->status ?? ''] ?? 'Encerrado' }}</span>
                                @endunless
                            </td>
                            <td>{{ $turma->etapa->nome ?? '—' }}</td>
                            <td>{{ \App\Models\Turma::TURNOS[$turma->turno ?? ''] ?? '—' }}</td>
                            <td>{{ $turma->sala ?? '—' }}</td>
                            <td>
                                @if($turma->estaAtiva())
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-secondary">Inativa</span>
                                @endif
                            </td>
                            <td>
                                @php $resultado = $resultados[$matricula->id] ?? null; @endphp
                                @if($resultado === 'aprovado')
                                    <span class="badge badge-success" title="Média em todas as disciplinas igual ou acima do corte de aprovação">Aprovado</span>
                                @elseif($resultado === 'reprovado')
                                    <span class="badge badge-danger" title="Média abaixo do corte em alguma disciplina">Reprovado</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('portal.turma', $turma->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAluno ? 9 : 10 }}" class="text-center text-muted py-4">
                                <i class="fas fa-chalkboard-teacher fa-2x mb-2 d-block"></i>
                                Nenhuma matrícula encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
