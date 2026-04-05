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
                    <div class="reserva-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i> Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i> Tabela
                        </button>
                    </div>
                    <div class="d-flex align-items-end">
                        {{ $reservas->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 reserva-index-body" data-view="cards">
            <div class="reserva-cards">
                @foreach ($reservas as $reserva)
                    @php
                        $totalCalculado = $reserva->valor_total_final;
                        if ($totalCalculado === null) {
                            $totalCalculado = $reserva->valor_total_com_desconto;
                        }
                        if ($totalCalculado === null) {
                            $totalCalculado = $reserva->valor_total_tarifa_base;
                        }
                        if ($totalCalculado === null && $reserva->tarifa_base && $reserva->quantidade_dias) {
                            $totalCalculado = $reserva->tarifa_base * $reserva->quantidade_dias;
                        }
                        $totalCalculado = (float) ($totalCalculado ?? 0);
                        $pagamentoEfetuado = ($reserva->pagamento_efetuado ?? 'N') === 'S';
                    @endphp
                    <article class="reserva-card" onclick="window.location='{{ route('reserva.show', $reserva->id) }}';">
                        <div class="reserva-card__header">
                            <div>
                                <span class="reserva-card__id">Reserva #{{ $reserva->id }}</span>
                                <div class="reserva-card__cliente">{{ $reserva->usuario?->nome ?? 'Sem cliente' }}</div>
                            </div>
                            <div class="reserva-card__badges">
                                <span class="badge badge-xs {{ $pagamentoEfetuado ? 'badge-success' : 'badge-warning' }}">
                                    {{ $pagamentoEfetuado ? 'Pago' : 'Pendente' }}
                                </span>
                                <span class="badge badge-xs bg-{{ $reserva->statusReserva?->cor }}" title="{{ $reserva->statusReserva?->descricao }}">
                                    {{ $reserva->statusReserva?->nome }}
                                </span>
                            </div>
                        </div>
                        <div class="reserva-card__body">
                            <div class="reserva-card__row">
                                <span class="reserva-card__label">Veiculo</span>
                                <span class="reserva-card__value">
                                    {{ $reserva->grupoFrota?->nome ?? '-' }} · {{ $reserva->modelos?->nome ?? 'Modelo nao definido' }}
                                </span>
                            </div>
                            <div class="reserva-card__row">
                                <span class="reserva-card__label">Retirada</span>
                                <span class="reserva-card__value">
                                    {{ $reserva->estacaoRetirada?->nome ?? '-' }} · {{ Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div class="reserva-card__row">
                                <span class="reserva-card__label">Devolucao</span>
                                <span class="reserva-card__value">
                                    {{ $reserva->estacaoDevolucao?->nome ?? '-' }} · {{ Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div class="reserva-card__row">
                                <span class="reserva-card__label">Total</span>
                                <span class="reserva-card__value">
                                    BRL {{ number_format($totalCalculado, 2, ',', '.') }}
                                    <small class="text-muted">({{ (int) ($reserva->quantidade_dias ?? 0) }} dias)</small>
                                </span>
                            </div>
                        </div>
                        <div class="reserva-card__footer">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('reserva.show', $reserva->id) }}">Ver detalhes</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="reserva-table table-responsive">
                <table class="table table-hover table-bordered table-sm">
    <thead class="thead-light">
        <tr>
            <th class="text-center">#</th>
            <th>Cliente</th>
            <th>Veiculo</th>
            <th>Retirada</th>
            <th>Devolucao</th>
            <th class="text-right">Total</th>
            <th>Pagamento</th>
            <th>Status</th>
            {{-- <th class="text-center">Ações</th> --}}
        </tr>
    </thead>
    <tbody>
        @foreach ($reservas as $reserva)
            @php
                $totalCalculado = $reserva->valor_total_final;
                if ($totalCalculado === null) {
                    $totalCalculado = $reserva->valor_total_com_desconto;
                }
                if ($totalCalculado === null) {
                    $totalCalculado = $reserva->valor_total_tarifa_base;
                }
                if ($totalCalculado === null && $reserva->tarifa_base && $reserva->quantidade_dias) {
                    $totalCalculado = $reserva->tarifa_base * $reserva->quantidade_dias;
                }
                $totalCalculado = (float) ($totalCalculado ?? 0);
                $pagamentoEfetuado = ($reserva->pagamento_efetuado ?? 'N') === 'S';
            @endphp
            <tr onclick="window.location='{{ route('reserva.show', $reserva->id) }}';" style="cursor: pointer;">
                <td class="text-center">{{ $reserva->id }}</td>
                <td>
                    <strong>{{ $reserva->usuario?->nome ?? 'Sem cliente' }}</strong><br>
                    <small>{{ $reserva->usuario?->email ?? '-' }}</small>
                </td>
                <td>
                    <strong>{{ $reserva->grupoFrota?->nome ?? '-' }}</strong><br>
                    <small>{{ $reserva->modelos?->nome ?? 'Modelo nao definido' }}</small>
                </td>
                <td>
                    <strong>{{ $reserva->estacaoRetirada?->nome }}</strong><br>
                    <small>{{ Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}</small>
                </td>
                <td>
                    <strong>{{ $reserva->estacaoDevolucao?->nome }}</strong><br>
                    <small>{{ Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</small>
                </td>
                <td class="text-right">
                    <strong>BRL {{ number_format($totalCalculado, 2, ',', '.') }}</strong><br>
                    <small>{{ (int) ($reserva->quantidade_dias ?? 0) }} dias</small>
                </td>
                <td>
                    <span class="badge badge-xs {{ $pagamentoEfetuado ? 'badge-success' : 'badge-warning' }}">
                        {{ $pagamentoEfetuado ? 'Pago' : 'Pendente' }}
                    </span>
                </td>
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
    </div>
@stop

@section('js')
    <script>
        (function () {
            const body = document.querySelector('.reserva-index-body');
            const buttons = document.querySelectorAll('.reserva-view-toggle [data-view]');
            if (!body || !buttons.length) return;

            function setView(view) {
                body.setAttribute('data-view', view);
                localStorage.setItem('reservaIndexView', view);
                buttons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.view === view);
                });
            }

            const saved = localStorage.getItem('reservaIndexView') || 'cards';
            setView(saved);

            buttons.forEach(btn => {
                btn.addEventListener('click', () => setView(btn.dataset.view));
            });
        })();
    </script>
@stop

@section('css')
<style>
    .reserva-view-toggle {
        display: flex;
        gap: 6px;
        margin-left: 12px;
    }

    .reserva-view-toggle .btn.active {
        background: #1f2937;
        color: #fff;
        border-color: #1f2937;
    }

    .reserva-index-body[data-view="table"] .reserva-cards {
        display: none;
    }

    .reserva-index-body[data-view="cards"] .reserva-table {
        display: none;
    }

    .reserva-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        padding: 6px 6px 12px;
    }

    .reserva-card {
        background: #ffffff;
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 16px;
        padding: 14px 16px;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .reserva-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.12);
    }

    .reserva-card__header {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .reserva-card__id {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        font-weight: 700;
        display: block;
    }

    .reserva-card__cliente {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .reserva-card__badges {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .reserva-card__body {
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-size: 0.88rem;
    }

    .reserva-card__row {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .reserva-card__label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8;
        font-weight: 700;
    }

    .reserva-card__value {
        color: #0f172a;
        font-weight: 600;
    }

    .reserva-card__footer {
        margin-top: 12px;
        display: flex;
        justify-content: flex-end;
    }

    @media (max-width: 768px) {
        .reserva-view-toggle {
            display: none;
        }
        .reserva-index-body {
            padding: 0 4px 8px;
        }
        .reserva-index-body[data-view="table"] .reserva-cards {
            display: grid;
        }
        .reserva-index-body[data-view="table"] .reserva-table {
            display: none;
        }
    }
</style>
@stop
