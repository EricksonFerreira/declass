@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-page-title :title="$titulo" subtitle="Próximos eventos das escolas dos seus alunos" />
@stop

@section('content')
@php
    $porDia = $eventos->groupBy(fn ($e) => $e->data_inicio->format('Y-m-d'));
    $tipoCores = ['reuniao' => 'primary', 'evento' => 'info', 'formatura' => 'purple', 'feriado' => 'secondary', 'avaliacao' => 'warning', 'prazo' => 'danger', 'outro' => 'dark'];
@endphp

<div class="card">
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
                    <div class="list-group-item">
                        <strong>{{ $evento->titulo }}</strong>
                        <span class="badge badge-{{ $tipoCores[$evento->tipo] ?? 'secondary' }} ml-1">{{ $evento->tipoLabel() }}</span>
                        <div class="small text-muted mt-1">
                            @if($evento->hora_inicio)
                                <i class="fa fa-clock"></i> {{ \Carbon\Carbon::parse($evento->hora_inicio)->format('H:i') }}
                                @if($evento->hora_fim) às {{ \Carbon\Carbon::parse($evento->hora_fim)->format('H:i') }}@endif ·
                            @endif
                            {{ $evento->escola->nome ?? 'Rede toda' }}
                            @if($evento->local) · {{ $evento->local }}@endif
                            @if($evento->data_fim && !$evento->data_fim->eq($evento->data_inicio))
                                · até {{ $evento->data_fim->format('d/m/Y') }}
                            @endif
                        </div>
                        @if($evento->descricao)
                            <div class="small mt-1">{{ \Illuminate\Support\Str::limit($evento->descricao, 300) }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-alt fa-3x mb-3 d-block"></i>
                Nenhum evento programado para as escolas dos seus alunos.
            </div>
        @endforelse
    </div>
</div>
@stop
