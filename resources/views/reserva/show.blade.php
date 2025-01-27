@extends('adminlte::page')

@section('title', 'Detalhes da Reserva')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    {{-- <x-page-title :title="$titulo" /> --}}
    <div class="d-flex justify-content-between">

        <div class="d-flex col">
            <h3 class="mb-0 font-weight-bold">{{ $titulo }}</h3>
        </div>

        <div class="col d-flex justify-content-end">
            @include('reserva.components.botoes_acoes_show_reserva')
        </div>
    </div>
@stop

@section('content')
    <div class="d-flex flex-wrap">
        <div class="col-md-7 mb-4">
            <div class="card card-secondary card-outline">
                <div class="card-header py-2">
                    <h3 class="card-title">Informações da Reserva</h3>
                </div>
                <div class="card-body p-2">
                    <div class="d-flex flex-wrap mb-2">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="far fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Retirada</span>
                                    <span class="info-box-number">{{ Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}</span>
                                    <span class="info-box-text">{{ $reserva->estacaoRetirada->nome ?? 'Local não especificado' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-calendar-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Devolução</span>
                                    <span class="info-box-number">{{ Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</span>
                                    <span class="info-box-text">{{ $reserva->estacaoDevolucao->nome ?? 'Local não especificado' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="info-box">
                                <div class="info-box-content">
                                    <small>
                                       <span class=""><i class="fas fa-clock"></i> Duração</span>
                                    </small>
                                    <h5 style="white-space: nowrap;">
                                        <strong>
                                            {{ $reserva->quantidade_dias }} dias
                                        </strong>
                                    </h5>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-box bg-{{ $reserva->statusReserva->cor }}">
                                <div class="info-box-content">
                                    <small>
                                       <span class=""><i class="fas fa-info-circle"></i> Status</span>
                                    </small>
                                    <span class="info-box-text font-weight-bold" style="white-space: normal">
                                        {{ strtoupper($reserva->statusReserva->nome) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card card-secondary card-outline">
                <div class="card-header ">
                    <h3 class="card-title mb-0"><i class="fas fa-car mr-2"></i>Detalhes do Veículo</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap">
                        <div class="col-md-4 mb-2">
                            <img src="{{ $reserva->modelos->imagem_url ?? asset('img/no-image.jpg') }}" alt="{{ $reserva->modelos->nome }}" class="img-fluid rounded mb-2">
                        </div>
                        <div class="col-md-8">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">Grupo:</span>
                                    <span>{{ $reserva->grupoFrota->nome }}</span>
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">Modelo:</span>
                                    <span>{{ $reserva->modelos->nome }}</span>
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">Marca:</span>
                                    @if($reserva->modelos->marca)
                                        <span>{{ $reserva->modelos->marca->nome }}</span>
                                    @else
                                        <span class="badge bg-danger">Não informado</span>
                                    @endif
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">Ano:</span>
                                    @if($reserva->modelos->ano)
                                        <span>{{ $reserva->modelos->ano }}</span>
                                    @else
                                        <span class="badge bg-danger">Não informado</span>
                                    @endif
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">Capacidade:</span>
                                    @if($reserva->modelos->capacidade)
                                        <span>{{ $reserva->modelos->capacidade }} pessoas</span>
                                    @else
                                        <span class="badge bg-danger">Não informado</span>
                                    @endif
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">Transmissão:</span>
                                    @if($reserva->modelos->transmissao)
                                        <span>{{ $reserva->modelos->transmissao }}</span>
                                    @else
                                        <span class="badge bg-danger">Não informado</span>
                                    @endif
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">Combustível:</span>
                                    @if($reserva->modelos->combustivel)
                                        <span>{{ $reserva->modelos->combustivel }}</span>
                                    @else
                                        <span class="badge bg-danger">Não informado</span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5 mb-4">
            <div class="d-flex flex-wrap">
                <div class="col-md-12 mb-3">
                    <div class="card card-secondary card-outline  ">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-user mr-2"></i>Informações do Cliente</h5>
                        </div>
                        <div class="card-body p-2">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-user-circle mr-2"></i>Nome:</span>
                                    <span class="font-weight-bold">{{ $reserva->usuario->nome }}</span>
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-envelope mr-2"></i>E-mail:</span>
                                    <span class="font-weight-bold">{{ $reserva->usuario->email }}</span>
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-phone mr-2"></i>Telefone:</span>
                                    <span class="font-weight-bold">{{ $reserva->usuario->telefone ?? 'Não informado' }}</span>
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-id-card mr-2"></i>Número de Identificação:</span>
                                    @if($reserva->usuario->numero_identificacao)
                                        {{ $reserva->usuario->numero_identificacao }}
                                    @else
                                        <span class="badge bg-danger">Não Informado</span>
                                    @endif
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-birthday-cake mr-2"></i>Data de Nascimento:</span>
                                    <span class="font-weight-bold">{{ $reserva->usuario->data_nascimento ? \Carbon\Carbon::parse($reserva->usuario->data_nascimento)->format('d/m/Y') : 'Não informada' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <div class="card card-secondary card-outline rounded-lg">
                        <div class="card-header  ">
                            <h3 class="card-title mb-0"><i class="fas fa-money-bill-wave mr-2"></i>Resumo Financeiro</h3>
                        </div>
                        <div class="card-body p-2">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span>Tarifa Base</span>
                                    <span class="badge bg-light text-dark">€ {{ number_format($reserva->tarifa_base, 2, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span>Valor Total Tarifa Base</span>
                                    <div class="d-flex">
                                        <span class="badge bg-dark text-dark">
                                            {{ $reserva->quantidade_dias }} dias
                                        </span>
                                        <span class="ml-1 badge bg-light text-dark">€ {{ number_format($reserva->valor_total_tarifa_base, 2, ',', '.') }}</span>
                                    </div>
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span>Valor Total dos Opcionais</span>
                                    <span class="badge bg-light text-dark">€ {{ number_format($reserva->valor_opcionais, 2, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span>Desconto {{ $reserva->cupom_desconto ? "<span class='badge bg-pink'>({$reserva->cupom_desconto->codigo})</span>" : '' }}</span>
                                    <div class="d-flex">
                                        <span class="badge bg-info">{{ number_format($reserva->percentual_desconto ?? 0, 2, ',', '.') }} %</span>
                                        <span class="ml-1 {{ isset($reserva->valor_total_com_desconto ) ? 'badge bg-success' : 'badge bg-danger' }}">-€ {{ number_format($reserva->valor_total_com_desconto  ?? 0, 2, ',', '.') }}</span>
                                    </div>
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">Valor Total</span>
                                    <span class="badge bg-primary">€ {{ number_format($reserva->valor_total_final , 2, ',', '.') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <div class="card card-info card-outline">
                        <div class="card-header py-1">
                            <h3 class="card-title">Opcionais Selecionados</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($reserva->opcionais as $opcional)
                                    <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                        {{-- {{ $opcional->nome }} --}}
                                        <span>{{ $opcional->nome }} {!! $opcional->tipo_cobranca == 'por_dia' ? "<span class='badge bg-secondary'>Diário</span>" : "<span class='badge bg-purple'>Integral</span>" !!}</span>
                                            <div class="d-flex">
                                        @if($opcional->tipo_cobranca == 'por_dia')
                                        <span class="badge bg-light">
                                            Valor Diário: € {{ $opcional->preco_por_dia }}
                                        </span>
                                        <span class="badge bg-dark text-dark ml-2">
                                            {{ $reserva->quantidade_dias }} dias
                                        </span>
                                        @else
                                        <span class="badge bg-light">
                                            Valor Fixo: € {{ $opcional->preco_integral }}
                                        </span>
                                        @endif
                                        <span class="badge bg-light ml-2">
                                            Quantidade: {{ $opcional->pivot->quantidade }}
                                        </span>
                                        <span class="badge badge-primary ml-2">€ {{ number_format($opcional->pivot->quantidade * $opcional->pivot->preco_total, 2, ',', '.') ?? 0}}</span>
                                    </div>
                                    </li>
                                @empty
                                    <li class="list-group-item py-1">Nenhum opcional selecionado</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stop

