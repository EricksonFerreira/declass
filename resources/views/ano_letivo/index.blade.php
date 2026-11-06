@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Períodos letivos por escola" :create-url="auth()->user()->hasPermissao('ano_letivo.create') ? route('ano_letivo.create') : null" :create-can="'ano_letivo.create'" />
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
                        <th>Ano</th>
                        <th>Escola</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Status</th>
                        <th>Periodicidade</th>
                        <th>Situação</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($anosLetivos as $anoLetivo)
                        <tr>
                            <td class="font-weight-bold">{{ $anoLetivo->ano }}</td>
                            <td>{{ $anoLetivo->escola->nome ?? '—' }}</td>
                            <td>{{ optional($anoLetivo->data_inicio)->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ optional($anoLetivo->data_fim)->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ \App\Models\AnoLetivo::STATUS[$anoLetivo->status] ?? $anoLetivo->status }}</td>
                            <td>{{ \App\Models\AnoLetivo::PERIODICIDADES[$anoLetivo->periodicidade] ?? '—' }}</td>
                            <td>
                                @if($anoLetivo->estaAtivo())
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('ano_letivo.edit')
                                    <a href="{{ route('ano_letivo.edit', $anoLetivo->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('ano_letivo.destroy')
                                    <x-delete-button :action="route('ano_letivo.destroy', $anoLetivo->id)" titulo="Excluir" mensagem="Excluir o ano letivo &quot;{{ $anoLetivo->ano }}&quot;? Turmas vinculadas impedem a exclusão." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-alt fa-2x mb-2 d-block"></i>
                                Nenhum ano letivo encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($anosLetivos->hasPages())
        <div class="card-footer">
            {{ $anosLetivos->links() }}
        </div>
    @endif
</div>
@stop
