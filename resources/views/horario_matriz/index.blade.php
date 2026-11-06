@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Matriz de Horários" subtitle="Faixas de horário por escola e turno — base das grades das turmas" />
@stop

@section('content')
@foreach($escolas as $escola)
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold"><i class="fas fa-school"></i> {{ $escola->nome }}</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Turno</th>
                        <th>Dias letivos</th>
                        <th>Aulas/dia</th>
                        <th>Horário</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($turnos as $valor => $label)
                        @php $cfg = $matriz[$escola->id][$valor] ?? null; @endphp
                        <tr>
                            <td class="font-weight-bold">{{ $label }}</td>
                            @if($cfg)
                                <td>
                                    @foreach($cfg['dias'] as $dia)
                                        <span class="badge badge-secondary">{{ \App\Models\HorarioSlot::diaLabel($dia) }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $cfg['slots']->pluck('ordem')->unique()->count() }}</td>
                                <td class="small text-muted">
                                    {{ $cfg['slots']->min('hora_inicio') }}–{{ $cfg['slots']->max('hora_fim') }}
                                </td>
                            @else
                                <td colspan="3" class="text-muted">Não configurado</td>
                            @endif
                            <td class="text-right">
                                @can('horario_matriz.edit')
                                    <a href="{{ route('horario_matriz.edit', [$escola->id, $valor]) }}" class="btn btn-xs {{ $cfg ? 'btn-warning' : 'btn-primary' }}">
                                        <i class="fa fa-{{ $cfg ? 'edit' : 'cog' }}"></i> {{ $cfg ? 'Editar' : 'Configurar' }}
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

@if($escolas->isEmpty())
    <div class="card"><div class="card-body text-center text-muted py-4">Nenhuma escola cadastrada.</div></div>
@endif
@stop
