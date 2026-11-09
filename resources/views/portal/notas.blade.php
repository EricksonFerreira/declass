@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Desempenho por disciplina nas avaliações lançadas" />
@stop

@section('content')
@if($matriculas->isEmpty())
    <div class="alert alert-info">Nenhuma matrícula ativa no momento.</div>
@else
    <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />

    @forelse($selecionadas as $matricula)
        @include('portal.partials.notas-aluno', [
            'matricula' => $matricula,
            'notasMatricula' => $notas->get($matricula->id, collect()),
            'mediasMatricula' => $medias->get($matricula->id, collect()),
        ])
    @empty
        <div class="alert alert-info">Nenhuma matrícula encontrada para os filtros aplicados.</div>
    @endforelse
@endif
@stop
