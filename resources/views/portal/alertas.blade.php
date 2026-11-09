@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Comunicados e ocorrências que a escola marcou para a família acompanhar" />
@stop

@section('content')
<x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />

<div class="card">
    <div class="card-header font-weight-bold"><i class="fas fa-exclamation-circle mr-1"></i> Ocorrências</div>
    <div class="list-group list-group-flush">
        @forelse($ocorrencias as $ocorrencia)
            @php $cores = ['elogio' => 'success', 'advertencia' => 'warning', 'ocorrencia' => 'info', 'suspensao' => 'danger', 'outro' => 'secondary']; @endphp
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-1">
                    <span class="badge badge-{{ $cores[$ocorrencia->tipo] ?? 'secondary' }}">{{ \App\Models\Ocorrencia::TIPOS[$ocorrencia->tipo] ?? $ocorrencia->tipo }}</span>
                    <small class="text-muted">{{ $ocorrencia->data?->format('d/m/Y') }} · {{ $ocorrencia->aluno->pessoa->nome ?? '' }}</small>
                </div>
                <p class="mb-1">{{ $ocorrencia->descricao }}</p>
                @if($ocorrencia->medida_adotada)
                    <small class="text-muted"><strong>Medida:</strong> {{ $ocorrencia->medida_adotada }}</small>
                @endif
            </div>
        @empty
            <div class="list-group-item text-muted text-center py-4">Nenhum alerta no momento. Tudo bem por aqui.</div>
        @endforelse
    </div>
</div>

<div class="alert alert-info small">
    <i class="fas fa-info-circle mr-1"></i> Comunicados gerais da escola ficam em <a href="{{ route('portal.avisos') }}">Avisos</a> e a programação em <a href="{{ route('portal.agenda') }}">Agenda</a>.
</div>
@stop
