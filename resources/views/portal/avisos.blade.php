@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-page-title :title="$titulo" subtitle="Comunicados das escolas dos alunos vinculados à sua conta" />
@stop

@section('content')
@if($avisos->isEmpty())
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-bullhorn fa-3x mb-3 d-block"></i>
            Nenhum aviso no momento.
        </div>
    </div>
@else
    @foreach($avisos as $aviso)
        @php
            $badgeTipo = ['info' => 'primary', 'urgente' => 'danger', 'evento' => 'success'][$aviso->tipo] ?? 'secondary';
        @endphp
        <div class="card card-outline card-{{ $badgeTipo }} mb-3">
            <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
                <h3 class="card-title font-weight-bold mb-0">{{ $aviso->titulo }}</h3>
                <span class="badge badge-{{ $badgeTipo }} ml-auto">{{ \App\Models\Aviso::TIPOS[$aviso->tipo] ?? $aviso->tipo }}</span>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2" style="font-size:.85rem;">
                    <i class="far fa-calendar-alt"></i> {{ optional($aviso->data_publicacao)->format('d/m/Y') }}
                    &nbsp;·&nbsp; {{ $aviso->escola->nome ?? '' }}
                    @if($aviso->turma)
                        &nbsp;·&nbsp; Turma {{ $aviso->turma->nome }}
                    @else
                        &nbsp;·&nbsp; Todos os alunos
                    @endif
                </p>
                <div style="white-space:pre-line;">{{ $aviso->mensagem }}</div>
            </div>
        </div>
    @endforeach
@endif

<div class="mt-2">
    <a href="{{ route('portal.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Voltar ao portal</a>
</div>
@stop
