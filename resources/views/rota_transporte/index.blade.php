@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Linhas e horários de cada rota" :create-url="auth()->user()->hasPermissao('rota_transporte.create') ? route('rota_transporte.create') : null" :create-can="'rota_transporte.create'" />
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
                        <th>Nome</th>
                        <th>Escola</th>
                        <th>Veículo</th>
                        <th>Horário ida</th>
                        <th>Horário volta</th>
                        <th>Paradas</th>
                        <th>Passageiros</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rotas as $rota)
                        <tr>
                            <td class="font-weight-bold">{{ $rota->nome }}</td>
                            <td>{{ $rota->escola->nome ?? '—' }}</td>
                            <td>{{ $rota->veiculo->placa ?? '—' }}</td>
                            <td class="text-nowrap">{{ $rota->horario_ida?->format('H:i') ?? '—' }}</td>
                            <td class="text-nowrap">{{ $rota->horario_volta?->format('H:i') ?? '—' }}</td>
                            <td class="text-center">{{ $rota->paradas->count() }}</td>
                            <td class="text-center">{{ $rota->passageiros_ativos }}</td>
                            <td>
                                @if($rota->esta_ativo)
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-secondary">Inativa</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('rota_transporte.edit')
                                    <a href="{{ route('rota_transporte.edit', $rota->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('rota_transporte.destroy')
                                    <x-delete-button :action="route('rota_transporte.destroy', $rota->id)" titulo="Excluir" mensagem="Excluir a rota &quot;{{ $rota->nome }}&quot;? As paradas e vínculos de passageiros também serão removidos." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-route fa-2x mb-2 d-block"></i>
                                Nenhuma rota cadastrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($rotas->hasPages())
        <div class="card-footer">
            {{ $rotas->links() }}
        </div>
    @endif
</div>
@stop
