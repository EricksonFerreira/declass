@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @include('financeiro.fluxo_caixa._styles')

    @php
        $financeiro = str_starts_with((string) Route::currentRouteName(), 'financeiro.');
        $routePrefix = $financeiro ? 'financeiro.' : '';
        $nomeFiltro = data_get($itensFiltered, 'nome.valor', '');
        $ativoFiltro = data_get($itensFiltered, 'ativo.valor', '');
        $totalAtivos = $cupom_descontos->getCollection()->where('ativo', 'S')->count();
        $totalInativos = $cupom_descontos->getCollection()->where('ativo', 'N')->count();
    @endphp

    <div class="fc-page">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card fc-card fc-filter mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route($routePrefix.'cupom_desconto.filtro') }}" class="row align-items-end">
                    <div class="col-lg-4 col-md-5 mb-2">
                        <label for="nome" class="fc-label">Nome</label>
                        <input type="text" name="nome_like" id="nome" class="form-control" value="{{ $nomeFiltro }}" placeholder="Campanha ou nome do cupom">
                    </div>
                    <div class="col-lg-3 col-md-4 mb-2">
                        <label for="ativo" class="fc-label">Status</label>
                        <select name="ativo" id="ativo" class="form-control">
                            <option value="">Todos</option>
                            <option value="S" {{ $ativoFiltro === 'S' ? 'selected' : '' }}>Ativo</option>
                            <option value="N" {{ $ativoFiltro === 'N' ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>
                    <div class="col-lg-5 col-md-3 mb-2 fc-actions justify-content-end">
                        <button type="submit" class="btn btn-primary fc-btn">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route($routePrefix.'cupom_desconto.removerFiltro') }}" class="btn btn-outline-secondary fc-btn">
                            <i class="fas fa-times"></i>
                        </a>
                        <a href="{{ route($routePrefix.'cupom_desconto.create') }}" class="btn btn-success fc-btn">
                            <i class="fas fa-plus"></i> Novo cupom
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--projetado h-100">
                    <div>
                        <span>Total de cupons</span>
                        <strong class="text-primary">{{ $cupom_descontos->total() }}</strong>
                    </div>
                    <small>Registros cadastrados</small>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--receita h-100">
                    <div>
                        <span>Ativos nesta pagina</span>
                        <strong class="text-success">{{ $totalAtivos }}</strong>
                    </div>
                    <small>Disponiveis para uso</small>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--despesa h-100">
                    <div>
                        <span>Inativos nesta pagina</span>
                        <strong class="text-danger">{{ $totalInativos }}</strong>
                    </div>
                    <small>Fora da operação</small>
                </div>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h5 class="fc-title">
                        <i class="fas fa-percentage"></i>
                        <span>Cupons de desconto<span class="fc-subtitle">Campanhas promocionais usadas nas reservas</span></span>
                    </h5>
                    <form action="{{ route($routePrefix.'cupom_desconto.setPerPage') }}" method="POST" class="mb-0">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table fc-table">
                    <thead>
                        <tr>
                            <th class="px-3">Nome</th>
                            <th>Codigo</th>
                            <th class="text-right">Valor</th>
                            <th>Status</th>
                            <th class="text-center">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cupom_descontos as $cupom_desconto)
                            <tr>
                                <td class="px-3 font-weight-bold">{{ $cupom_desconto->nome }}</td>
                                <td>
                                    <span class="fc-badge bg-success-light">
                                        <i class="fas fa-ticket-alt"></i>{{ $cupom_desconto?->codigo ?: '-' }}
                                    </span>
                                </td>
                                <td class="text-right font-weight-bold text-primary">{{ $cupom_desconto?->valor }}%</td>
                                <td>
                                    <span class="fc-badge {{ $cupom_desconto->ativo === 'S' ? 'fc-badge--pago' : 'fc-badge--cancelado' }}">
                                        {{ $cupom_desconto->ativo === 'S' ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route($routePrefix.'cupom_desconto.edit', $cupom_desconto->id) }}" class="btn btn-sm btn-outline-primary fc-btn">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <x-delete-button :action="route($routePrefix.'cupom_desconto.destroy', $cupom_desconto->id)" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fas fa-ticket-alt d-block mb-3" style="font-size: 2.4rem; opacity: .18;"></i>
                                    Nenhum cupom encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end">
                {{ $cupom_descontos->links() }}
            </div>
        </div>
    </div>
@stop
