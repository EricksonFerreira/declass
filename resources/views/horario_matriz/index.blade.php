@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Matriz de Horários" subtitle="Grades de horário nomeadas por escola e turno — cada turma se vincula a uma" />
@stop

@section('content')
@foreach($escolas as $escola)
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold"><i class="fas fa-school"></i> {{ $escola->nome }}</span>
            @can('horario_matriz.create')
                <a href="{{ route('horario_matriz.create', $escola->id) }}" class="btn btn-xs btn-primary">
                    <i class="fa fa-plus"></i> Novo horário
                </a>
            @endcan
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Horário</th>
                        <th>Turno</th>
                        <th>Dias letivos</th>
                        <th>Aulas/dia</th>
                        <th>Turmas</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($horarios[$escola->id] as $h)
                        <tr>
                            <td class="font-weight-bold">{{ $h->nome }}</td>
                            <td>{{ $turnos[$h->turno] ?? $h->turno }}</td>
                            <td>
                                @foreach($h->slots->pluck('dia_semana')->unique()->sort() as $dia)
                                    <span class="badge badge-secondary">{{ \App\Models\HorarioSlot::diaLabel($dia) }}</span>
                                @endforeach
                            </td>
                            <td>{{ $h->slots->groupBy('dia_semana')->map->count()->max() ?? 0 }}</td>
                            <td>{{ $h->turmas_count }}</td>
                            <td class="text-right">
                                @can('horario_matriz.edit')
                                    <a href="{{ route('horario_matriz.edit', $h->id) }}" class="btn btn-xs btn-warning">
                                        <i class="fa fa-edit"></i> Editar
                                    </a>
                                @endcan
                                @can('horario_matriz.destroy')
                                    @if(!$h->turmas_count)
                                        <form method="POST" action="{{ route('horario_matriz.destroy', $h->id) }}" class="d-inline"
                                            onsubmit="return confirm('Excluir o horário {{ addslashes($h->nome) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted text-center py-3">Nenhum horário cadastrado nesta escola.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach

@if($escolas->isEmpty())
    <div class="card"><div class="card-body text-center text-muted py-4">Nenhuma escola cadastrada.</div></div>
@endif
@stop
