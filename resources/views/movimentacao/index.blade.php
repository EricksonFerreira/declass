@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('movimentacao.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $movimentacaos->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('movimentacao.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $movimentacaos->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo de Movimentação</th>
                        <th>Usuário</th>
                        <th>Status</th>
                        <th>Veículo</th>
                        <th>Grupo de Veículo</th>
                        <th>Data de Registro</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movimentacaos as $movimentacao)
                        <tr>
                            <td>{{$movimentacao->id}}</td>
                            <td>{{ $movimentacao->tipoMovimentacao?->nome }}</td>
                            <td>{{ $movimentacao->usuario?->nome }}</td>
                            <td>
                                <span class="badge badge-pill bg-{{ $movimentacao->statusMovimentacao?->cor ?? 'secondary' }}">
                                    {{ $movimentacao->statusMovimentacao?->nome }}
                                </span>
                            </td>
                            <td>
                                @if($movimentacao->veiculo)
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text"><i class="fas fa-car mr-1"></i> {{ $movimentacao->veiculo->modelo->nome }}</span>
                                            <span class="info-box-number">{{ $movimentacao->veiculo->placa }}</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" style="width: 100%"></div>
                                            </div>
                                            <span class="progress-description">
                                                <small>
                                                    <span data-toggle="tooltip" title="Ano do veículo">
                                                        <i class="fas fa-calendar-alt mr-1"></i> {{ $movimentacao->veiculo->ano ?? 'N/A' }}
                                                    </span> |
                                                    <span data-toggle="tooltip" title="Cor do veículo">
                                                        <i class="fas fa-palette mr-1"></i> {{ $movimentacao->veiculo->cor ?? 'N/A' }}
                                                    </span> |
                                                    <span data-toggle="tooltip" title="Número do chassi">
                                                        <i class="fas fa-fingerprint mr-1"></i> {{ $movimentacao->veiculo->chassi ?? 'N/A' }}
                                                    </span>
                                                </small>
                                            </span>
                                        </div>
                                    </div>
                                @else
                                   <span class="text-muted">Não definido</span>
                                @endif
                            </td>
                            <td>
                                @if($movimentacao->veiculo && $movimentacao->veiculo->grupoFrota)
                                    {{ $movimentacao->veiculo->grupoFrota->nome }}
                                @else
                                    <span class="text-muted">Não definido</span>
                                @endif
                            </td>
                            <td>
                                @if($movimentacao->data_registro)
                                    <i class="fas fa-car mr-1"></i>
                                    {{ Carbon\Carbon::parse($movimentacao->data_registro)->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-muted">Não definida</span>
                                @endif
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('movimentacao.edit', $movimentacao->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('movimentacao.destroy', $movimentacao->id)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script></script>
@stop
