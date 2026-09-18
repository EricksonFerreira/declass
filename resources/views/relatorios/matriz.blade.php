@extends('adminlte::page')

@section('title', 'Matriz de Horas')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Matriz de Horas — Colaborador × Projeto'" />
@stop

@section('css')
<style>
.matriz-table th, .matriz-table td { white-space: nowrap; font-size: .82rem; }
.matriz-table .col-membro { position: sticky; left: 0; background: #fff; z-index: 1; min-width: 200px; }
.matriz-table thead .col-membro { background: #f4f6f9; z-index: 2; }
.matriz-table .proj-head { max-width: 160px; overflow: hidden; text-overflow: ellipsis; vertical-align: bottom !important; }
.matriz-table .proj-nome { display: block; max-width: 150px; overflow: hidden; text-overflow: ellipsis; }
.matriz-cell { text-align: right; }
.matriz-cell.zero { color: #cbd5e1; }
</style>
@stop

@section('content')
@php
    $fmtHora = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.');
@endphp

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('relatorios.matriz') }}" class="form-inline flex-wrap" style="gap:.5rem;">
            <input type="text" name="busca" class="form-control form-control-sm" placeholder="Buscar projeto..."
                   value="{{ $filtros['busca'] ?? '' }}" style="width:200px;">
            <input type="date" name="data_inicio" class="form-control form-control-sm" title="Lançamentos a partir de"
                   value="{{ $filtros['data_inicio'] ?? '' }}">
            <input type="date" name="data_fim" class="form-control form-control-sm" title="Lançamentos até"
                   value="{{ $filtros['data_fim'] ?? '' }}">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filtrar</button>
            <a href="{{ route('relatorios.matriz') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
        </form>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-sm mb-0 matriz-table">
            <thead class="thead-light">
                <tr>
                    <th class="col-membro pl-3">Colaborador</th>
                    @foreach ($projetos as $projeto)
                        <th class="proj-head text-right" title="{{ $projeto->nome }}">
                            <span class="proj-nome">{{ $projeto->nome }}</span>
                            @if ($projeto->codigo)
                                <small class="text-muted font-weight-normal">{{ $projeto->codigo }}</small>
                            @endif
                        </th>
                    @endforeach
                    <th class="text-right pr-3 border-left font-weight-bold">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($membros as $uid => $membro)
                    <tr>
                        <td class="col-membro pl-3">
                            <div class="font-weight-bold">{{ $membro['info']['nome'] }}</div>
                            <small class="text-muted">{{ $membro['info']['funcao_nome'] }}</small>
                        </td>
                        @foreach ($projetos as $projeto)
                            @php $h = $membro['horas'][$projeto->id] ?? null; @endphp
                            <td class="matriz-cell {{ $h ? '' : 'zero' }}">{{ $fmtHora($h) }}</td>
                        @endforeach
                        <td class="matriz-cell pr-3 border-left font-weight-bold">{{ $fmtHora($totaisMembro[$uid] ?? 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($projetos) + 2 }}" class="text-center text-muted py-4">
                            <i class="fas fa-th fa-2x mb-2 d-block"></i>
                            Nenhuma hora lançada nos projetos exibidos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($membros)
                <tfoot class="thead-light">
                    <tr>
                        <th class="col-membro pl-3">Total por projeto</th>
                        @foreach ($projetos as $projeto)
                            <th class="text-right">{{ $fmtHora($totaisProjeto[$projeto->id] ?? null) }}</th>
                        @endforeach
                        <th class="text-right pr-3 border-left font-weight-bold">{{ $fmtHora(array_sum($totaisMembro)) }}</th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="card-footer text-muted" style="font-size:.78rem;">
        <i class="fas fa-info-circle mr-1"></i>
        Cada célula mostra as <strong>horas apontadas</strong> pelo colaborador no projeto (rastreador/timesheet).
        Respeita a visibilidade de projetos privados e o recorte por liderança.
    </div>
</div>
@endsection
