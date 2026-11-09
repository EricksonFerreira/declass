@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Percentual de presença por disciplina" />
@stop

@section('content')
@if($matriculas->isEmpty())
    <div class="alert alert-info">Nenhuma matrícula ativa no momento.</div>
@else
    <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />

    @forelse($blocos as $bloco)
        @include('portal.partials.frequencia-aluno', ['bloco' => $bloco])
    @empty
        <div class="alert alert-info">Nenhuma matrícula encontrada para os filtros aplicados.</div>
    @endforelse
@endif
@stop
