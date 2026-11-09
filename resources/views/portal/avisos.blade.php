@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Comunicados das escolas dos alunos vinculados à sua conta" />
@stop

@section('content')
@php
    $corTipo = ['info' => 'info', 'urgente' => 'danger', 'evento' => 'success'];
    $iconeTipo = ['info' => 'fa-info-circle', 'urgente' => 'fa-exclamation-triangle', 'evento' => 'fa-calendar-check'];
@endphp

<x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />

@forelse($avisos as $aviso)
    @php
        $cor = $corTipo[$aviso->tipo] ?? 'secondary';
        $icone = $iconeTipo[$aviso->tipo] ?? 'fa-bullhorn';
        $novo = $aviso->data_publicacao && $aviso->data_publicacao->gte(now()->subDays(7)->startOfDay());
    @endphp
    <div class="card card-outline card-{{ $cor }}">
        <div class="card-header py-2 d-flex flex-wrap align-items-center" style="gap:.75rem;">
            <div class="flex-grow-1" style="min-width:200px;">
                <i class="fas {{ $icone }} text-{{ $cor }}"></i>
                <strong>{{ $aviso->titulo }}</strong>
                <div>
                    <span class="badge badge-{{ $cor }}">{{ \App\Models\Aviso::TIPOS[$aviso->tipo] ?? $aviso->tipo }}</span>
                    @if($novo)
                        <span class="badge badge-warning">Novo</span>
                    @endif
                    <span class="badge badge-secondary">{{ $aviso->escola->nome ?? '' }}</span>
                    <span class="badge badge-light border">
                        {{ $aviso->turma ? 'Turma '.$aviso->turma->nome : 'Todos os alunos' }}
                    </span>
                </div>
            </div>
            <div class="text-right text-nowrap">
                <small class="text-muted">
                    <i class="far fa-calendar-alt mr-1"></i>{{ optional($aviso->data_publicacao)->format('d/m/Y') }}
                </small>
            </div>
        </div>
        <div class="card-body py-2">
            <p class="mb-0" style="white-space: pre-line;"><small>{{ $aviso->mensagem }}</small></p>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-bullhorn fa-3x mb-3 d-block"></i>
            Nenhum aviso no momento. Quando a escola publicar um comunicado, ele aparece aqui.
        </div>
    </div>
@endforelse
@stop
