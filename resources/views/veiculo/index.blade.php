@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Frota de transporte das unidades" :create-url="auth()->user()->hasPermissao('veiculo.create') ? route('veiculo.create') : null" :create-can="'veiculo.create'" />
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
                        <th>Placa</th>
                        <th>Modelo</th>
                        <th>Ano</th>
                        <th>Capacidade</th>
                        <th>Motorista</th>
                        <th>Escola</th>
                        <th>Passageiros</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($veiculos as $veiculo)
                        <tr>
                            <td class="font-weight-bold">{{ $veiculo->placa }}</td>
                            <td>{{ $veiculo->modelo }}</td>
                            <td>{{ $veiculo->ano ?? '—' }}</td>
                            <td>{{ $veiculo->capacidade ?? '—' }}</td>
                            <td>
                                {{ $veiculo->motorista_nome ?? '—' }}
                                @if($veiculo->motorista_telefone)
                                    <br><small class="text-muted">{{ $veiculo->motorista_telefone }}</small>
                                @endif
                            </td>
                            <td>{{ $veiculo->escola->nome ?? '—' }}</td>
                            <td class="text-center">{{ $veiculo->passageiros() }}</td>
                            <td>
                                @if($veiculo->estaAtivo())
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('veiculo.edit')
                                    <a href="{{ route('veiculo.edit', $veiculo->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('veiculo.destroy')
                                    <x-delete-button :action="route('veiculo.destroy', $veiculo->id)" titulo="Excluir" mensagem="Excluir o veículo &quot;{{ $veiculo->placa }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-bus fa-2x mb-2 d-block"></i>
                                Nenhum veículo cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($veiculos->hasPages())
        <div class="card-footer">
            {{ $veiculos->links() }}
        </div>
    @endif
</div>
@stop
