@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Calendário de provas, trabalhos e avaliações por turma" />
@stop

@section('content')
@php
    $porDia = $avaliacoes->groupBy(fn ($a) => $a->data->format('Y-m-d'));
    $tipoCores = ['prova' => 'danger', 'trabalho' => 'info', 'participacao' => 'success', 'simulado' => 'warning', 'projeto' => 'primary', 'recuperacao' => 'dark', 'outro' => 'secondary'];
    $tipoLabel = \App\Models\Avaliacao::TIPOS;
@endphp

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="btn-group">
            <a href="{{ route('avaliacao.agenda', array_merge($filtros, ['mes' => $mesAnterior])) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-chevron-left"></i></a>
            <a href="{{ route('avaliacao.agenda', array_merge($filtros, ['mes' => $mesAtual])) }}" class="btn btn-sm btn-outline-secondary">Hoje</a>
            <a href="{{ route('avaliacao.agenda', array_merge($filtros, ['mes' => $mesProximo])) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-chevron-right"></i></a>
        </div>
        <h5 class="mb-0 font-weight-bold">{{ $mesCarbon->locale('pt_BR')->translatedFormat('F \d\e Y') }}</h5>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('avaliacao.agenda') }}" class="form-inline flex-wrap mb-3" style="gap:.5rem;">
            <input type="hidden" name="mes" value="{{ $mesCarbon->format('Y-m') }}">
            <select name="escola_id" class="form-control form-control-sm">
                <option value="">Todas as escolas</option>
                @foreach($escolas as $escola)
                    <option value="{{ $escola->id }}" {{ ($filtros['escola_id'] ?? '') == $escola->id ? 'selected' : '' }}>{{ $escola->nome }}</option>
                @endforeach
            </select>
            <select name="turma_id" class="form-control form-control-sm">
                <option value="">Todas as turmas</option>
                @foreach($turmas as $turma)
                    <option value="{{ $turma->id }}" {{ ($filtros['turma_id'] ?? '') == $turma->id ? 'selected' : '' }}>{{ $turma->nome }}</option>
                @endforeach
            </select>
            <select name="tipo" class="form-control form-control-sm">
                <option value="">Todos os tipos</option>
                @foreach($tipos as $val => $label)
                    <option value="{{ $val }}" {{ ($filtros['tipo'] ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
            <a href="{{ route('avaliacao.agenda', ['mes' => $mesCarbon->format('Y-m')]) }}" class="btn btn-sm btn-secondary">Limpar</a>
        </form>

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
                Nenhuma avaliação neste mês{{ count(array_filter($filtros)) ? ' com os filtros aplicados' : '' }}.
            </div>
        @endforelse
    </div>
</div>
@stop
