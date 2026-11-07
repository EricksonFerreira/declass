@extends('adminlte::page')

@section('title', $titulo)

@php $isProfessor = auth()->user()->isProfessor(); @endphp

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Chamada — '.$diario->data?->format('d/m/Y')" :subtitle="$diario->turma->nome.' · '.$diario->disciplina->nome.' · '.$diario->professor->nome" />
@stop

@section('content')
<div class="card">
    <div class="card-header font-weight-bold">
        Alunos matriculados ({{ $matriculas->count() }})
        @if($diario->conteudo)
            <small class="text-muted d-block font-weight-normal">{{ \Illuminate\Support\Str::limit($diario->conteudo, 120) }}</small>
        @endif
    </div>
    <div class="card-body p-0">
        <form method="POST" action="{{ route('diario.frequencia.salvar', $diario->id) }}">
            @csrf
            @method('PUT')
            @include('diario.partials.tabela-chamada')

            @if($matriculas->isNotEmpty())
                <div class="card-footer d-flex" style="gap:.5rem;">
                    <button type="submit" class="btn btn-primary">Salvar chamada</button>
                    <a href="{{ route('diario.index') }}" class="btn btn-secondary">Voltar</a>
                </div>
            @endif
        </form>
    </div>
</div>
@stop
