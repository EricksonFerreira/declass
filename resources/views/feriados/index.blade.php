@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('feriado.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">
                    REGISTROS <span class="badge badge-success">{{ $feriados->total() }}</span>
                </h3>
                <div class="d-flex align-items-center" style="gap:6px;">
                    <form action="{{ route('feriado.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $feriados->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Nome</th>
                        <th>Estação</th>
                        <th>Status</th>
                        <th style="width:180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($feriados as $feriado)
                        <tr>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($feriado->data)->format('d/m/Y') }}</strong>
                                <div class="text-muted" style="font-size:.75rem;">
                                    {{ \Carbon\Carbon::parse($feriado->data)->locale('pt_BR')->isoFormat('dddd') }}
                                </div>
                            </td>
                            <td>{{ $feriado->nome }}</td>
                            <td>{{ $feriado->estacao?->nome ?? '—' }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $feriado->ativo === 'S' ? 'success' : 'danger' }}">
                                    {{ $feriado->ativo === 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width:180px;">
                                <a href="{{ route('feriado.edit', $feriado->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fa fa-edit"></i> Editar
                                </a>
                                <x-delete-button :action="route('feriado.destroy', $feriado->id)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                Nenhum feriado cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
<style>
    .table td { vertical-align: middle; }
</style>
@stop

@section('js')
<script></script>
@stop
