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
            <div class="btn-group" role="group" aria-label="Ações da Reserva">
                <a href="{{ route('reserva.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>

                @if(in_array($reserva->statusReserva->id, [1])) {{--  Criada  --}}
                    <a href="{{ route('reserva.edit', $reserva->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit mr-1"></i> Editar Reserva
                    </a>
                @endif

                @if(in_array($reserva->statusReserva->id, [1])) {{--  Criada  --}}
                    <a href="{{ route('reserva.efetuarPagamento', $reserva->id) }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-credit-card mr-1"></i> Efetuar Pagamento
                    </a>
                @endif

                @if(in_array($reserva->statusReserva->id, [2])) {{--  Confirmada  --}}
                    <button type="button" class="btn btn-sm btn-outline-warning" id="btnCheckInReserva">
                        <i class="fas fa-check mr-1"></i> Check-In
                    </button>
                @endif

                @if(in_array($reserva->statusReserva->id, [1,2])) {{--  Criada ou Confirmada  --}}
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnCancelarReserva">
                        <i class="fas fa-times mr-1"></i> Cancelar Reserva
                    </button>
                @endif

                @if(in_array($reserva->statusReserva->id, [4])) {{--  Em andamento  --}}
                    <button type="button" class="btn btn-sm btn-outline-success" id="btnCheckOutReserva">
                        <i class="fas fa-check-circle mr-1"></i> Check-Out
                    </button>
                @endif
            </div>
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
                                            {{ Carbon\Carbon::parse($reserva->data_inicio)->diffInDays(Carbon\Carbon::parse($reserva->data_fim)) }} dias
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
                                    <span>Valor Total por Dias</span>
                                    <span class="badge bg-light text-dark">€ {{ number_format($reserva->valor_total, 2, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span>Opcionais</span>
                                    <span class="badge bg-light text-dark">€ {{ number_format($reserva->total_opcional, 2, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span>Desconto {{ $reserva->cupom_desconto ? "<span class='badge bg-success'>({$reserva->cupom_desconto->codigo})</span>" : '' }}</span>
                                    <span class="{{ isset($reserva->valor_desconto) ? 'badge bg-success' : 'badge bg-danger' }}">-€ {{ number_format($reserva->valor_desconto ?? 0, 2, ',', '.') }}</span>
                                </li>
                                <li class="list-group-item py-1 d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">Valor Total</span>
                                    <span class="badge bg-primary">€ {{ number_format(($reserva->valor_total + $reserva->total_opcional) - ($reserva->valor_desconto ?? 0), 2, ',', '.') }}</span>
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
                                        {{ $opcional->nome }}
                                        <span class="badge badge-primary badge-pill">€ {{ number_format($opcional->pivot->preco_total, 2, ',', '.') ?? 0}}</span>
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
    <script>
        document.getElementById('btnCancelarReserva').addEventListener('click', function() {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Você não poderá reverter esta ação!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, cancelar reserva!',
                cancelButtonText: 'Não, manter reserva'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aqui você pode adicionar a lógica para cancelar a reserva
                    // Por exemplo, fazer uma requisição AJAX para o servidor
                    // Após o cancelamento bem-sucedido, você pode mostrar uma mensagem de sucesso:
                    Swal.fire(
                        'Cancelada!',
                        'A reserva foi cancelada com sucesso.',
                        'success'
                    )
                }
            })
        });
    </script>
@stop
