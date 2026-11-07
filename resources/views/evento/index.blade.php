@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Calendário de eventos e compromissos da escola" :create-url="auth()->user()->hasPermissao('evento.create') ? route('evento.create') : null" :create-can="'evento.create'" />
@stop

@section('content')
@php
    $porDia = $eventos->groupBy(fn ($e) => $e->data_inicio->format('Y-m-d'));
    $tipoCores = ['reuniao' => 'primary', 'evento' => 'info', 'formatura' => 'purple', 'feriado' => 'secondary', 'avaliacao' => 'warning', 'prazo' => 'danger', 'outro' => 'dark'];
    $statusCores = ['agendado' => 'info', 'realizado' => 'success', 'cancelado' => 'secondary'];
@endphp

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="btn-group">
            <a href="{{ route('evento.index', ['mes' => $mesAnterior]) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-chevron-left"></i></a>
            <a href="{{ route('evento.index', ['mes' => $mesAtual]) }}" class="btn btn-sm btn-outline-secondary">Hoje</a>
            <a href="{{ route('evento.index', ['mes' => $mesProximo]) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-chevron-right"></i></a>
        </div>
        <h5 class="mb-0 font-weight-bold">{{ $mesCarbon->locale('pt_BR')->translatedFormat('F \d\e Y') }}</h5>
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
    </div>

    <div class="card-body">
        @forelse($porDia as $dia => $eventosDoDia)
            @php $dataDia = \Carbon\Carbon::parse($dia); @endphp
            <div class="d-flex align-items-center mb-2 mt-3">
                <span class="badge badge-dark p-2">{{ $dataDia->format('d/m/Y') }}</span>
                <span class="ml-2 text-muted small">{{ $dataDia->locale('pt_BR')->translatedFormat('l') }}</span>
                @if($dataDia->isToday())
                    <span class="badge badge-success ml-2">Hoje</span>
                @endif
            </div>
            <div class="list-group mb-3">
                @foreach($eventosDoDia as $evento)
                    <div class="list-group-item d-flex align-items-start flex-wrap" style="gap:.5rem;">
                        <div class="flex-grow-1">
                            <div>
                                <strong>{{ $evento->titulo }}</strong>
                                <span class="badge badge-{{ $tipoCores[$evento->tipo] ?? 'secondary' }} ml-1">{{ $evento->tipoLabel() }}</span>
                                <span class="badge badge-{{ $statusCores[$evento->status] ?? 'secondary' }} ml-1">{{ $evento->statusLabel() }}</span>
                                @if($evento->publico === 'todos')
                                    <span class="badge badge-outline-primary ml-1" title="Visível para as famílias"><i class="fa fa-users"></i> famílias</span>
                                @endif
                                @if($evento->suspende_aulas === 'S')
                                    <span class="badge badge-danger ml-1" title="Suspende as aulas do dia"><i class="fa fa-ban"></i> sem aula</span>
                                @endif
                                @if($evento->turmas->isNotEmpty())
                                    <span class="badge badge-info ml-1" title="{{ $evento->turmas->pluck('nome')->implode(', ') }}"><i class="fa fa-chalkboard-teacher"></i> {{ $evento->turmas->count() }} turma(s)</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                @if($evento->hora_inicio)
                                    {{ \Carbon\Carbon::parse($evento->hora_inicio)->format('H:i') }}
                                    @if($evento->hora_fim) às {{ \Carbon\Carbon::parse($evento->hora_fim)->format('H:i') }}@endif
                                    ·
                                @endif
                                {{ $evento->escola->nome ?? 'Rede toda' }}
                                @if($evento->local) · {{ $evento->local }}@endif
                                @if($evento->data_fim && !$evento->data_fim->eq($evento->data_inicio))
                                    · até {{ $evento->data_fim->format('d/m/Y') }}
                                @endif
                            </small>
                            @if($evento->descricao)
                                <div class="small mt-1">{{ \Illuminate\Support\Str::limit($evento->descricao, 200) }}</div>
                            @endif
                        </div>
                        <div class="text-right">
                            @can('evento.edit')
                                <a href="{{ route('evento.edit', $evento->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                            @endcan
                            @can('evento.destroy')
                                <x-delete-button :action="route('evento.destroy', $evento->id)" titulo="Excluir" mensagem="Excluir o evento &quot;{{ $evento->titulo }}&quot;?" />
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-alt fa-3x mb-3 d-block"></i>
                Nenhum evento neste mês{{ count($itensFiltered ?? []) ? ' com os filtros aplicados' : '' }}.
            </div>
        @endforelse
    </div>
</div>
@stop
