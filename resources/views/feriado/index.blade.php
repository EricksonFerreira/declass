@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Feriados" subtitle="Dias sem aula ou com registro especial — alimentam o calendário letivo" />
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <form method="GET" action="{{ route('feriado.index') }}" class="form-inline">
            <select name="escola_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Todas as escolas</option>
                @foreach($escolas as $id => $nome)
                    <option value="{{ $id }}" {{ (string) ($filtros['escola_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $nome }}</option>
                @endforeach
            </select>
            <select name="ano" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Todos os anos</option>
                @foreach($anos as $a => $label)
                    <option value="{{ $a }}" {{ (string) ($filtros['ano'] ?? '') === (string) $a ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </form>
        @can('feriado.create')
            <a href="{{ route('feriado.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Novo feriado</a>
        @endcan
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Data</th>
                    <th>Nome</th>
                    <th>Escola</th>
                    <th>Aulas</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feriados as $f)
                    <tr class="{{ $f->data_inicio->isPast() ? 'text-muted' : '' }}">
                        <td class="text-nowrap">
                            {{ $f->data_inicio->format('d/m/Y') }}
                            @if($f->data_fim) — {{ $f->data_fim->format('d/m/Y') }} @endif
                        </td>
                        <td class="font-weight-bold">{{ $f->nome }}</td>
                        <td>{{ $f->escola->nome ?? 'Toda a rede' }}</td>
                        <td>
                            @if($f->suspende_aulas === 'S')
                                <span class="badge badge-danger">Suspensas</span>
                            @else
                                <span class="badge badge-secondary">Aula normal</span>
                            @endif
                        </td>
                        <td class="text-right">
                            @can('feriado.edit')
                                <a href="{{ route('feriado.edit', $f->id) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a>
                            @endcan
                            @can('feriado.destroy')
                                <form method="POST" action="{{ route('feriado.destroy', $f->id) }}" class="d-inline"
                                    onsubmit="return confirm('Excluir {{ addslashes($f->nome) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Nenhum feriado cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
