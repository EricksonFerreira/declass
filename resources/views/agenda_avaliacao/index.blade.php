@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Calendário de provas, trabalhos e avaliações por turma" />
@stop

@push('css')
<style>
    .agenda-cal { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; }
    .cal-head {
        background: #f1f3f5; text-align: center; font-size: .7rem; font-weight: 700;
        letter-spacing: .05em; text-transform: uppercase; color: #6c757d; padding: .45rem .25rem;
    }
    .cal-cell { background: #fff; min-height: 104px; padding: .3rem .35rem; }
    .cal-cell.cal-fora { background: #f8f9fa; }
    .cal-cell.cal-fora .cal-num { color: #c3c8cd; }
    .cal-cell.cal-hoje { background: #f0f7ff; box-shadow: inset 0 0 0 2px #007bff; }
    .cal-num {
        display: inline-flex; align-items: center; justify-content: center;
        width: 22px; height: 22px; border-radius: 50%; font-size: .78rem; font-weight: 700; color: #495057;
    }
    .cal-num.hoje { background: #007bff; color: #fff; }
    .cal-evt {
        display: block; margin-top: 2px; padding: 1px 5px; font-size: 11px; line-height: 1.45;
        border-radius: 3px; border-left: 3px solid transparent;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .cal-evt:hover { filter: brightness(.94); text-decoration: none; }
    .cal-mais { display: block; margin-top: 2px; padding-left: 5px; font-size: 10px; color: #6c757d; }
    @media (max-width: 575.98px) {
        .cal-cell { min-height: 62px; }
        .cal-evt { font-size: 10px; }
    }
</style>
@endpush

@section('content')
@php
    $porDia = $avaliacoes->groupBy(fn ($a) => $a->data->format('Y-m-d'));
    $tipoEstilos = [
        'prova' => ['#dc2626', '#fee2e2'],
        'trabalho' => ['#0284c7', '#e0f2fe'],
        'participacao' => ['#16a34a', '#dcfce7'],
        'simulado' => ['#d97706', '#fef3c7'],
        'projeto' => ['#2563eb', '#dbeafe'],
        'recuperacao' => ['#7c3aed', '#ede9fe'],
        'outro' => ['#475569', '#f1f5f9'],
    ];
    $tipoCores = ['prova' => 'danger', 'trabalho' => 'info', 'participacao' => 'success', 'simulado' => 'warning', 'projeto' => 'primary', 'recuperacao' => 'dark', 'outro' => 'secondary'];
    $tipoLabel = \App\Models\Avaliacao::TIPOS;
    $diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
    $podeEditar = auth()->user()->hasPermissao('avaliacao.edit');
    $temFiltro = ! empty($itensFiltered);
@endphp

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="btn-group">
            <a href="{{ route('avaliacao.agenda', ['mes' => $mesAnterior]) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-chevron-left"></i></a>
            <a href="{{ route('avaliacao.agenda', ['mes' => $mesAtual]) }}" class="btn btn-sm btn-outline-secondary">Hoje</a>
            <a href="{{ route('avaliacao.agenda', ['mes' => $mesProximo]) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-chevron-right"></i></a>
        </div>
        <h5 class="mb-0 font-weight-bold">{{ $mesCarbon->locale('pt_BR')->translatedFormat('F \d\e Y') }}</h5>
    </div>
    <div class="card-header d-flex flex-wrap align-items-center py-2 border-bottom">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
    </div>

    <div class="card-body p-0">
        <ul class="nav nav-tabs px-3 pt-2" id="agendaAvTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tab-calendario" role="tab"><i class="far fa-calendar-alt mr-1"></i> Calendário</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-lista" role="tab"><i class="fas fa-list-ul mr-1"></i> Lista <span class="badge badge-light border ml-1">{{ $avaliacoes->count() }}</span></a>
            </li>
        </ul>

        <div class="tab-content p-2 p-md-3">
            {{-- Visao calendario --}}
            <div class="tab-pane fade show active" id="tab-calendario" role="tabpanel">
                <div class="agenda-cal">
                    @foreach($diasSemana as $ds)
                        <div class="cal-head">{{ $ds }}</div>
                    @endforeach
                    @foreach($semanas as $semana)
                        @foreach($semana as $dia)
                            <div class="cal-cell {{ $dia['do_mes'] ? '' : 'cal-fora' }} {{ $dia['data']->isToday() ? 'cal-hoje' : '' }}">
                                <div class="cal-num {{ $dia['data']->isToday() ? 'hoje' : '' }}">{{ $dia['data']->day }}</div>
                                @foreach($dia['avaliacoes']->take(4) as $avaliacao)
                                    @php $est = $tipoEstilos[$avaliacao->tipo] ?? $tipoEstilos['outro']; @endphp
                                    @php $hint = $avaliacao->nome.' — '.$avaliacao->turma->nome.($avaliacao->disciplina ? ' · '.$avaliacao->disciplina->nome : ''); @endphp
                                    <a href="{{ $podeEditar ? route('avaliacao.edit', $avaliacao->id) : '#' }}" class="cal-evt"
                                        style="background: {{ $est[1] }}; border-left-color: {{ $est[0] }}; color: {{ $est[0] }}"
                                        title="{{ $hint }}">
                                        {{ $avaliacao->nome }}
                                    </a>
                                @endforeach
                                @if($dia['avaliacoes']->count() > 4)
                                    <span class="cal-mais">+{{ $dia['avaliacoes']->count() - 4 }} mais</span>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
                @if($avaliacoes->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-check fa-2x mb-2 d-block"></i>
                        Nenhuma avaliação neste mês{{ $temFiltro ? ' com os filtros aplicados' : '' }}.
                    </div>
                @endif
            </div>

            {{-- Visao lista --}}
            <div class="tab-pane fade" id="tab-lista" role="tabpanel">
                @forelse($porDia as $dia => $avaliacoesDoDia)
                    @php $dataDia = \Carbon\Carbon::parse($dia); @endphp
                    <div class="d-flex align-items-center mb-2 mt-3">
                        <span class="badge badge-dark p-2">{{ $dataDia->format('d/m/Y') }}</span>
                        <span class="ml-2 text-muted small">{{ $dataDia->locale('pt_BR')->translatedFormat('l') }}</span>
                        @if($dataDia->isToday())
                            <span class="badge badge-success ml-2">Hoje</span>
                        @endif
                    </div>
                    <div class="list-group mb-3">
                        @foreach($avaliacoesDoDia as $avaliacao)
                            <div class="list-group-item d-flex align-items-start flex-wrap" style="gap:.5rem;">
                                <div class="flex-grow-1">
                                    <div>
                                        <strong>{{ $avaliacao->nome }}</strong>
                                        <span class="badge badge-{{ $tipoCores[$avaliacao->tipo] ?? 'secondary' }} ml-1">{{ $tipoLabel[$avaliacao->tipo] ?? $avaliacao->tipo }}</span>
                                        @if($avaliacao->periodo)
                                            <span class="badge badge-outline-secondary ml-1">{{ $avaliacao->periodo }}</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        {{ $avaliacao->turma->nome ?? '—' }}
                                        @if($avaliacao->turma?->etapa) · {{ $avaliacao->turma->etapa->nome }}@endif
                                        · {{ $avaliacao->turma?->escola->nome ?? '—' }}
                                        @if($avaliacao->disciplina) · {{ $avaliacao->disciplina->nome }}@endif
                                        @if($avaliacao->professor) · {{ $avaliacao->professor->name }}@endif
                                        @if($avaliacao->nota_maxima !== null) · máx. {{ $avaliacao->nota_maxima }}@endif
                                    </small>
                                    @if($avaliacao->descricao)
                                        <div class="small mt-1">{{ \Illuminate\Support\Str::limit($avaliacao->descricao, 200) }}</div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    @can('avaliacao.edit')
                                        <a href="{{ route('avaliacao.edit', $avaliacao->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-calendar-check fa-3x mb-3 d-block"></i>
                        Nenhuma avaliação neste mês{{ $temFiltro ? ' com os filtros aplicados' : '' }}.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@stop
