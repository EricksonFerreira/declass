@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Bimestres, trimestres ou semestres de cada ano letivo" :create-url="auth()->user()->hasPermissao('periodo_letivo.create') ? route('periodo_letivo.create') : null" :create-can="'periodo_letivo.create'" />
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Ordem</th>
                        <th>Período</th>
                        <th>Ano letivo</th>
                        <th>Escola</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodos as $periodo)
                        <tr>
                            <td class="text-center font-weight-bold">{{ $periodo->ordem }}º</td>
                            <td class="font-weight-bold">{{ $periodo->nome }}</td>
                            <td>{{ $periodo->anoLetivo->ano ?? '—' }}</td>
                            <td>{{ $periodo->escola->nome ?? '—' }}</td>
                            <td class="text-nowrap">{{ optional($periodo->data_inicio)->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-nowrap">{{ optional($periodo->data_fim)->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if($periodo->estaAtivo())
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('periodo_letivo.edit')
                                    <a href="{{ route('periodo_letivo.edit', $periodo->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('periodo_letivo.destroy')
                                    <x-delete-button :action="route('periodo_letivo.destroy', $periodo->id)" titulo="Excluir" mensagem="Excluir o período &quot;{{ $periodo->nome }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-week fa-2x mb-2 d-block"></i>
                                Nenhum período letivo cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($periodos->hasPages())
        <div class="card-footer">
            {{ $periodos->links() }}
        </div>
    @endif
</div>
@stop
