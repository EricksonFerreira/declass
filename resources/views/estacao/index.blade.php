@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('estacao.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif

    <div class="card p-2 card-estacoes-index">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span class="badge badge-success">{{ $estacaos->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('estacao.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $estacaos->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 table-responsive">
            <table class="table table-striped table-bordered table-sm table-estacoes-index">
                <thead>
                    <tr>
                        <th>Estacao</th>
                        <th>Contato</th>
                        <th>Localizacao</th>
                        <th>Operacao</th>
                        <th>Status</th>
                        <th style="width: 250px;">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estacaos as $estacao)
                        <tr>
                            <td>
                                <div class="estacao-nome text-bold">{{ $estacao->nome }}</div>
                                <small class="text-muted">ID: {{ $estacao->id }}</small>
                            </td>
                            <td>
                                <div><i class="fa fa-envelope mr-1 text-muted"></i>{{ $estacao->email ?? 'Nao definido' }}</div>
                                <div><i class="fa fa-mobile-alt mr-1 text-muted"></i>{{ $estacao->celular ?? 'Nao definido' }}</div>
                                <div><i class="fa fa-phone mr-1 text-muted"></i>{{ $estacao->telefone ?? 'Nao definido' }}</div>
                            </td>
                            <td>
                                <div>{{ $estacao->pais?->nome ?? 'Pais nao definido' }}</div>
                                <div>{{ $estacao->cidade ?? 'Cidade nao definida' }}</div>
                                <small class="text-muted">{{ $estacao->endereco ?? 'Endereco nao definido' }}{{ $estacao->numero ? ', ' . $estacao->numero : '' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-light border mr-1">Check-in: {{ $estacao->checkin == 'S' ? 'Sim' : 'Nao' }}</span>
                                <span class="badge badge-light border">Check-out: {{ $estacao->checkout == 'S' ? 'Sim' : 'Nao' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-xs bg-{{ $estacao->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $estacao->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 250px;">
                                <a href="{{ route('estacao.edit', $estacao->id) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('estacao.destroy', $estacao->id)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table-estacoes-index td {
            vertical-align: middle;
        }

        .estacao-nome {
            color: #0f2740;
        }

        .table-estacoes-index .badge-light {
            color: #334155;
            background: #f8fafc;
            font-weight: 600;
        }

        @media (max-width: 991.98px) {
            .table-estacoes-index {
                min-width: 980px;
            }
        }
    </style>
@stop

@section('js')
    <script></script>
@stop
