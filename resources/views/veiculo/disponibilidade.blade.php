@extends('adminlte::page')

@section('title', 'Disponibilidade de Veículos')

@section('content_header')
    <h1>Disponibilidade de Veículos</h1>
@stop

@section('content')
    <div class="row">
        <!-- Cards de filtro -->
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-car"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Todos</span>
                    <span class="info-box-number">{{ $totalVeiculos }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Disponíveis</span>
                    <span class="info-box-number">{{ $veiculosDisponiveis }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Reservados</span>
                    <span class="info-box-number">{{ $veiculosReservados }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-ban"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Indisponíveis</span>
                    <span class="info-box-number">{{ $veiculosIndisponiveis }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Disponibilidade Mensal</h3>
            <div class="card-tools">
                <form action="{{ route('veiculo.disponibilidade') }}" method="GET" class="form-inline">
                    <select name="mes" class="form-control mr-2">
                        @foreach(range(1, 12) as $mes)
                            <option value="{{ $mes }}" {{ request('mes') == $mes ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($mes)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </form>
            </div>
        </div>
        <div class="card-body table-responsive p-0" style="height: 500px;">
            <table class="table table-head-fixed text-nowrap">
                <thead>
                    <tr>
                        <th class="sticky-column" style="position: sticky; left: 0; z-index: 2; background-color: #fff;">Grupo</th>
                        <th class="sticky-column" style="position: sticky; left: 120px; z-index: 2; background-color: #fff;">Modelo</th>
                        <th class="sticky-column" style="position: sticky; left: 240px; z-index: 2; background-color: #fff;">Placa</th>
                        @foreach($diasDoMes as $dia)
                            <th>{{ $dia }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($veiculos as $veiculo)
                        <tr>
                            <td class="sticky-column" style="position: sticky; left: 0; z-index: 1; background-color: #fff;">{{ $veiculo->grupoFrota->nome ?? null}}</td>
                            <td class="sticky-column" style="position: sticky; left: 120px; z-index: 1; background-color: #fff;">{{ $veiculo->modelo->nome ?? null }}</td>
                            <td class="sticky-column" style="position: sticky; left: 240px; z-index: 1; background-color: #fff;">{{ $veiculo->placa_licenca ?? null }}</td>
                            @foreach($diasDoMes as $dia)
                                <td>
                                    @if($veiculo->estaDisponivel($dia))
                                        <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-ban"></i></span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
        .table-head-fixed thead th {
            position: sticky;
            top: 0;
            background-color: #fff;
            z-index: 1;
        }
    </style>
@stop

@section('js')
    <script>
        $(function () {
            // Adicione aqui qualquer JavaScript necessário
        });
    </script>
@stop
