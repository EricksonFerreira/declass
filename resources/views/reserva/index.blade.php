@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('reserva.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $reservas->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('reserva.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $reservas->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-hover table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center">#</th>
                        <th>Retirada</th>
                        <th>Devolução</th>
                        <th>Veículo</th>
                        <th>Cliente</th>
                        <th>Status</th>
                        {{-- <th class="text-center">Ações</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reservas as $reserva)
                        <tr onclick="window.location='{{ route('reserva.show', $reserva->id) }}';" style="cursor: pointer;">
                            <td class="text-center">{{ $reserva->id }}</td>
                            <td>
                                <strong>{{ $reserva->estacaoRetirada?->nome }}</strong><br>
                                <small>{{ Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <strong>{{ $reserva->estacaoDevolucao?->nome }}</strong><br>
                                <small>{{ Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <strong>{{$reserva->grupoFrota?->nome}}</strong><br>
                                <small>{{$reserva->modelos?->nome}}</small>
                            </td>
                            <td>{{ $reserva->usuario?->nome}}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $reserva->statusReserva?->cor }}" style="cursor: help;" data-toggle="tooltip" title="{{ $reserva->statusReserva?->descricao }}">
                                    {{ $reserva->statusReserva?->nome }}
                                </span>
                            </td>
                            {{-- <td class="text-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-xs btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-cog mr-2"></i> Ações
                                    </button>
                                    <div class="dropdown-menu" style="font-size:12px !important; overflow:scroll">
                                        <a class="dropdown-item" href="{{ route('reserva.show', $reserva->id) }}">
                                            <i class="fas fa-eye mr-2"></i> Visualizar
                                        </a>
                                        @if ($reserva->pagamento_efetuado == 'N')
                                            <a class="dropdown-item" href="{{ route('reserva.efetuarPagamento', $reserva->id) }}">
                                                <i class="fas fa-credit-card mr-2"></i> Efetuar Pagamento
                                            </a>
                                        @endif
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-warning" href="{{ route('reserva.edit', $reserva->id) }}">
                                            <i class="fas fa-edit mr-2"></i> Editar
                                        </a>
                                        <x-delete-button :action="route('reserva.destroy', $reserva->id)" />
                                    </div>
                                </div>
                            </td> --}}
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
