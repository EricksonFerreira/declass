@extends('adminlte::page')

@section('title', $titulo ?? 'Disponibilidade de Veículos')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo ?? 'Disponibilidade de Veículos'" />
@stop

@section('content')
    <div class="availability-page">

        <div class="availability-hero card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form action="{{ route('veiculo.disponibilidade') }}" method="GET" class="row align-items-end">

                    <div class="col-lg-2 col-md-3 mb-2">
                        <label class="text-uppercase small font-weight-bold text-muted mb-1">Ano</label>
                        <select name="ano" class="form-control form-control-sm">
                            @foreach ($anos as $anoOption)
                                <option value="{{ $anoOption }}"
                                    {{ (string) request('ano', $ano ?? date('Y')) === (string) $anoOption ? 'selected' : '' }}>
                                    {{ $anoOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-3 mb-2">
                        <label class="text-uppercase small font-weight-bold text-muted mb-1">Mês</label>
                        <select name="mes" class="form-control form-control-sm">
                            @foreach ($meses as $mesOption => $nomeMes)
                                <option value="{{ $mesOption }}"
                                    {{ (int) request('mes', $mes ?? date('n')) === (int) $mesOption ? 'selected' : '' }}>
                                    {{ $nomeMes }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-3 mb-2">
                        <label class="text-uppercase small font-weight-bold text-muted mb-1">Estação</label>
                        <select name="estacao_id" class="form-control form-control-sm">
                            <option value="">Todas as estações</option>
                            <option value="sem_estacao" {{ request('estacao_id') === 'sem_estacao' ? 'selected' : '' }}>
                                Sem estação
                            </option>

                            @foreach ($estacoes as $id => $nome)
                                <option value="{{ $id }}"
                                    {{ (string) request('estacao_id') === (string) $id ? 'selected' : '' }}>
                                    {{ $nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-3 mb-2">
                        <label class="text-uppercase small font-weight-bold text-muted mb-1">Buscar veículo</label>
                        <input id="tableSearch" type="text" class="form-control form-control-sm"
                            placeholder="Placa, modelo, grupo ou estação...">
                    </div>

                    <div class="col-lg-2 mb-2 d-flex">
                        <button type="submit" class="btn btn-primary btn-sm mr-2">
                            <i class="fas fa-filter mr-1"></i>Aplicar
                        </button>

                        <a href="{{ route('veiculo.disponibilidade') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-undo mr-1"></i>Limpar
                        </a>
                    </div>

                </form>
            </div>
        </div>

        <div class="row mb-3">

            <div class="col-xl-3 col-md-6 mb-2">
                <button type="button" class="kpi-card kpi-total is-active js-status-filter" data-status="all">
                    <div class="kpi-icon">
                        <i class="fas fa-car-side"></i>
                    </div>

                    <div class="kpi-meta">
                        <span class="kpi-label">Todos os veículos</span>
                        <strong class="kpi-value">{{ $totalVeiculos ?? 0 }}</strong>
                        <small class="kpi-subtitle">100% da frota exibida</small>
                    </div>
                </button>
            </div>

            <div class="col-xl-3 col-md-6 mb-2">
                <button type="button" class="kpi-card kpi-available js-status-filter" data-status="available">
                    <div class="kpi-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>

                    <div class="kpi-meta">
                        <span class="kpi-label">Disponíveis</span>
                        <strong class="kpi-value">{{ $veiculosDisponiveis ?? 0 }}</strong>
                        <small class="kpi-subtitle">100% livres no mês</small>
                    </div>
                </button>
            </div>

            <div class="col-xl-3 col-md-6 mb-2">
                <button type="button" class="kpi-card kpi-partial js-status-filter" data-status="partial">
                    <div class="kpi-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>

                    <div class="kpi-meta">
                        <span class="kpi-label">Parcialmente disponíveis</span>
                        <strong class="kpi-value">{{ $veiculosParciais ?? 0 }}</strong>
                        <small class="kpi-subtitle">Com bloqueios por horário</small>
                    </div>
                </button>
            </div>

            <div class="col-xl-3 col-md-6 mb-2">
                <button type="button" class="kpi-card kpi-unavailable js-status-filter" data-status="unavailable">
                    <div class="kpi-icon">
                        <i class="fas fa-ban"></i>
                    </div>

                    <div class="kpi-meta">
                        <span class="kpi-label">Indisponíveis</span>
                        <strong class="kpi-value">{{ $veiculosIndisponiveis ?? 0 }}</strong>
                        <small class="kpi-subtitle">Sem disponibilidade no mês</small>
                    </div>
                </button>
            </div>

        </div>

        <div class="card shadow-sm border-0 availability-card">
            <div class="card-header bg-white border-bottom-0 pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title font-weight-bold mb-1">Grade mensal de disponibilidade</h3>

                        <div class="small text-muted">
                            A disponibilidade é calculada por horário. Dias parciais possuem detalhes na linha do tempo.
                            @if (!empty($reservaBloqueioHoras))
                                <span class="ml-1">
                                    Bloqueio pós-reserva: <strong>{{ $reservaBloqueioHoras }}h</strong>.
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="legend d-flex flex-wrap mt-2 mt-md-0">
                        <span class="legend-item">
                            <i class="fas fa-circle legend-ok"></i> Disponível
                        </span>

                        <span class="legend-item">
                            <i class="fas fa-circle legend-partial"></i> Parcial
                        </span>

                        <span class="legend-item">
                            <i class="fas fa-circle legend-reserved"></i> Reservado
                        </span>

                        <span class="legend-item">
                            <i class="fas fa-circle legend-overdue"></i> Reserva não finalizada
                        </span>

                        <span class="legend-item">
                            <i class="fas fa-circle legend-off"></i> Indisponível
                        </span>

                        <span class="legend-item">
                            <i class="fas fa-circle legend-pending"></i> Sem local
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="availability-table-wrap">
                    <table class="table table-hover table-sm text-nowrap mb-0 availability-table">
                        <thead>
                            <tr>
                                <th class="sticky-column col-group">Grupo / Modelo</th>
                                <th class="sticky-column col-name">Nome</th>
                                <th class="sticky-column col-plate">Placa</th>
                                <th class="sticky-column col-station">Estação</th>
                                <th class="sticky-column col-ratio text-center"
                                    title="Percentual de disponibilidade no mês">
                                    Disp.
                                </th>

                                @foreach ($diasDoMes as $dia)
                                    @php
                                        $dataDia = \Carbon\Carbon::create(
                                            (int) request('ano', $ano ?? date('Y')),
                                            (int) request('mes', $mes ?? date('n')),
                                            (int) $dia,
                                        );

                                        $diaSemana = mb_substr($dataDia->locale('pt_BR')->isoFormat('ddd'), 0, 1);
                                        $isHoje = $dataDia->isToday();
                                    @endphp

                                    <th class="text-center day-col {{ $isHoje ? 'is-today' : '' }}">
                                        <span class="day-week">{{ mb_strtoupper($diaSemana) }}</span>
                                        <span class="day-number">{{ str_pad((string) $dia, 2, '0', STR_PAD_LEFT) }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody id="availabilityRows">
                            @forelse($veiculos as $veiculo)
                                @php
                                    $grupo = trim((string) ($veiculo->grupoFrota->nome ?? 'Sem grupo'));
                                    $modelo = trim((string) ($veiculo->modelo->nome ?? 'Sem modelo'));
                                    $placa = trim((string) ($veiculo->placa_licenca ?? 'Sem placa'));
                                    $estacao = optional($veiculo->localDisponibilidade)->nome ?? 'Sem estação';

                                    $resumo = $resumoVeiculos[$veiculo->id] ?? [
                                        'percentual' => 0,
                                        'status_mes' => 'off',
                                        'dias_parciais' => 0,
                                        'dias_indisponiveis' => 0,
                                    ];

                                    $ratio = (int) ($resumo['percentual'] ?? 0);

                                    $rowStatus = match ($resumo['status_mes'] ?? 'off') {
                                        'ok' => 'available',
                                        'partial' => 'partial',
                                        default => 'unavailable',
                                    };

                                    $ratioClass = match (true) {
                                        $ratio >= 90 => 'ratio-high',
                                        $ratio >= 40 => 'ratio-medium',
                                        default => 'ratio-low',
                                    };

                                    $searchText = strtolower(
                                        $grupo .
                                            ' ' .
                                            $modelo .
                                            ' ' .
                                            $placa .
                                            ' ' .
                                            $estacao .
                                            ' ' .
                                            ($veiculo->nome ?? ''),
                                    );
                                @endphp

                                <tr data-search="{{ $searchText }}" data-status="{{ $rowStatus }}">
                                    <td class="sticky-column col-group">
                                        <div class="vehicle-meta">
                                            <span class="meta-badge meta-group" title="{{ $grupo }}">
                                                <i class="fas fa-layer-group mr-1"></i>{{ $grupo }}
                                            </span>

                                            <span class="meta-badge meta-model" title="{{ $modelo }}">
                                                <i class="fas fa-car-side mr-1"></i>{{ $modelo }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="sticky-column col-name">
                                        <a href="{{ route('veiculo.show', $veiculo->id) }}" class="vehicle-name-link">
                                            <span
                                                class="vehicle-name">{{ $veiculo->nome ?? 'Veículo #' . $veiculo->id }}</span>
                                        </a>
                                    </td>

                                    <td class="sticky-column col-plate">
                                        <span class="plate-badge">{{ $placa }}</span>
                                    </td>

                                    <td class="sticky-column col-station">
                                        <span
                                            class="station-badge {{ $estacao === 'Sem estação' ? 'station-badge-danger' : 'station-badge-success' }}">
                                            {{ $estacao }}
                                        </span>
                                    </td>

                                    <td class="sticky-column col-ratio text-center">
                                        <span class="ratio-pill {{ $ratioClass }}"
                                            title="Disponibilidade mensal calculada por minutos">
                                            {{ $ratio }}%
                                        </span>
                                    </td>

                                    @foreach ($diasDoMes as $dia)
                                        @php
                                            $cell = $grade[$veiculo->id][$dia] ?? [
                                                'tipo' => 'off',
                                                'titulo' => 'Indisponível',
                                                'reserva_id' => null,
                                                'reserva_codigo' => null,
                                                'percentual_disponivel' => 0,
                                                'intervalos' => [],
                                            ];

                                            $tipo = $cell['tipo'] ?? 'off';
                                            $tituloCell = $cell['titulo'] ?? 'Indisponível';
                                            $percentualDia = (int) ($cell['percentual_disponivel'] ?? 0);
                                            $intervalosJson = json_encode($cell['intervalos'] ?? [], JSON_UNESCAPED_UNICODE);
                                            $diaFormatado = str_pad((string) $dia, 2, '0', STR_PAD_LEFT);

                                            $hasReserva = !empty($cell['reserva_id']);
                                            $reservaUrl = $hasReserva
                                                ? route('reserva.show', $cell['reserva_id'])
                                                : null;
                                        @endphp

                                        <td class="text-center availability-day-cell">
                                            @if ($tipo === 'reserved' && $hasReserva)
                                                <a href="{{ $reservaUrl }}" class="status-dot is-reserved"
                                                    title="{{ $tituloCell }}" style="text-decoration: none;">
                                                    <i class="fas fa-calendar-check"></i>
                                                </a>
                                            @elseif ($tipo === 'overdue' && $hasReserva)
                                                <a href="{{ $reservaUrl }}" class="status-dot is-overdue"
                                                    title="{{ $tituloCell }}" style="text-decoration: none;">
                                                    <i class="fas fa-clock"></i>
                                                </a>
                                            @else
                                                <button type="button"
                                                    class="status-dot is-{{ $tipo }} js-day-detail"
                                                    title="{{ $tituloCell }}"
                                                    data-veiculo="{{ $veiculo->nome ?? 'Veículo #' . $veiculo->id }}"
                                                    data-placa="{{ $placa }}" data-modelo="{{ $modelo }}"
                                                    data-estacao="{{ $estacao }}" data-dia="{{ $diaFormatado }}"
                                                    data-titulo="{{ $tituloCell }}" data-tipo="{{ $tipo }}"
                                                    data-percentual="{{ $percentualDia }}"
                                                    data-intervalos='{{ $intervalosJson }}'>
                                                    @if ($tipo === 'ok')
                                                        <i class="fas fa-check"></i>
                                                    @elseif ($tipo === 'partial')
                                                        <i class="fas fa-adjust"></i>
                                                    @elseif ($tipo === 'pending')
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    @else
                                                        <i class="fas fa-times"></i>
                                                    @endif
                                                </button>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 5 + count($diasDoMes) }}" class="text-center text-muted py-4">
                                        Nenhum veículo encontrado para o período selecionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center">
                <small class="text-muted">
                    Arraste horizontalmente para ver todos os dias do mês. Clique nos dias para ver a programação por
                    horário.
                </small>

                <small class="text-muted">
                    <span id="visibleRowsCount">{{ method_exists($veiculos, 'count') ? $veiculos->count() : 0 }}</span>
                    veículo(s) em exibição
                </small>
            </div>
        </div>

        <div id="availabilityDetailPanel" class="availability-detail-panel">
            <div class="availability-detail-header">
                <div>
                    <strong id="detailTitle">Detalhes do dia</strong>
                    <div class="small text-muted" id="detailSubtitle">-</div>
                </div>

                <button type="button" class="btn btn-sm btn-light" id="closeAvailabilityDetail">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="availability-detail-body">
                <div class="vehicle-detail-card mb-3">
                    <div class="vehicle-detail-icon">
                        <i class="fas fa-car-side"></i>
                    </div>

                    <div>
                        <div class="font-weight-bold" id="detailVehicle">-</div>
                        <div class="small text-muted" id="detailVehicleMeta">-</div>
                        <span class="station-badge station-badge-success mt-1" id="detailStation">-</span>
                    </div>
                </div>

                <div class="day-status-box">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Status do dia</div>
                            <div class="font-weight-bold" id="detailStatus">-</div>
                        </div>

                        <div class="detail-percent-badge">
                            <span id="detailPercent">0</span>%
                        </div>
                    </div>
                </div>

                <div class="font-weight-bold mb-2">Linha do tempo do dia</div>

                <div class="timeline-bar" id="detailTimelineBar"></div>

                <div class="timeline-labels">
                    <span>00:00</span>
                    <span>06:00</span>
                    <span>12:00</span>
                    <span>18:00</span>
                    <span>23:59</span>
                </div>

                <div class="font-weight-bold mb-2">Programação por horário</div>

                <div class="schedule-list" id="detailScheduleList"></div>
            </div>
        </div>

        <div id="availabilityPanelBackdrop" class="availability-panel-backdrop"></div>
    </div>
@stop

@section('css')
    <style>
        .availability-page {
            --color-ok: #16a34a;
            --color-partial: #f59e0b;
            --color-reserved: #d97706;
            --color-overdue: #92400e;
            --color-off: #dc2626;
            --color-pending: #64748b;
            --color-blue: #2563eb;
            --color-text: #0f172a;
            --color-muted: #64748b;
            --color-line: #e2e8f0;
        }

        .availability-hero {
            background: linear-gradient(120deg, #f8fafc 0%, #eef2ff 100%);
            border-radius: 14px;
        }

        .kpi-card {
            width: 100%;
            border: 0;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            color: var(--color-text);
            box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
            text-align: left;
            min-height: 96px;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(15, 23, 42, .13);
        }

        .kpi-card .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex: 0 0 auto;
        }

        .kpi-meta .kpi-label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .03em;
            opacity: .9;
            font-weight: 700;
        }

        .kpi-meta .kpi-value {
            display: block;
            font-size: 28px;
            line-height: 1.05;
            margin-top: 2px;
        }

        .kpi-subtitle {
            display: block;
            color: #475569;
            margin-top: 2px;
        }

        .kpi-total {
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        }

        .kpi-total .kpi-icon {
            background: #94a3b8;
            color: #fff;
        }

        .kpi-available {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        }

        .kpi-available .kpi-icon {
            background: var(--color-ok);
            color: #fff;
        }

        .kpi-partial {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
        }

        .kpi-partial .kpi-icon {
            background: var(--color-partial);
            color: #fff;
        }

        .kpi-unavailable {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
        }

        .kpi-unavailable .kpi-icon {
            background: var(--color-off);
            color: #fff;
        }

        .kpi-card.is-active {
            outline: 2px solid rgba(15, 23, 42, .35);
            filter: saturate(1.08);
        }

        .availability-card {
            border-radius: 16px;
            overflow: hidden;
        }

        .legend {
            gap: 14px;
            font-size: 12px;
            color: #475569;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .legend-item i {
            font-size: 9px;
        }

        .legend-ok {
            color: var(--color-ok);
        }

        .legend-partial {
            color: var(--color-partial);
        }

        .legend-reserved {
            color: var(--color-reserved);
        }

        .legend-overdue {
            color: var(--color-overdue);
        }

        .legend-off {
            color: var(--color-off);
        }

        .legend-pending {
            color: var(--color-pending);
        }

        .availability-table-wrap {
            max-height: 72vh;
            overflow: auto;
            position: relative;
            border-top: 1px solid var(--color-line);
        }

        .availability-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .availability-table thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
            font-size: 11px;
            text-transform: uppercase;
            color: #334155;
            letter-spacing: .02em;
            vertical-align: middle;
        }

        .availability-table thead .sticky-column {
            z-index: 35;
            background: #f8fafc;
        }

        .availability-table tbody td {
            vertical-align: middle;
            background: #fff;
            border-top: 1px solid #edf2f7;
        }

        .availability-table tbody tr:hover td {
            background: #f8fafc;
        }

        .sticky-column {
            position: sticky;
            background: #fff;
            z-index: 12;
            box-shadow: 1px 0 0 var(--color-line);
        }

        .col-group {
            left: 0;
            min-width: 230px;
            max-width: 230px;
        }

        .col-name {
            left: 230px;
            min-width: 120px;
            max-width: 120px;
        }

        .col-plate {
            left: 350px;
            min-width: 100px;
            max-width: 100px;
        }

        .col-station {
            left: 450px;
            min-width: 145px;
            max-width: 145px;
        }

        .col-ratio {
            left: 595px;
            min-width: 75px;
            max-width: 75px;
        }

        .day-col {
            min-width: 44px;
            max-width: 44px;
            padding: 6px 4px !important;
        }

        .day-week,
        .day-number {
            display: block;
            line-height: 1.1;
        }

        .day-week {
            color: #94a3b8;
            font-size: 10px;
            margin-bottom: 3px;
        }

        .day-number {
            font-weight: 800;
            color: #334155;
        }

        .day-col.is-today .day-number {
            background: var(--color-blue);
            color: #fff;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
        }

        .vehicle-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .meta-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
            max-width: 205px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .meta-group {
            background: #eef4ff;
            color: #3157a5;
            border: 1px solid #d7e4ff;
        }

        .meta-model {
            background: #f3f4f6;
            color: #444;
            border: 1px solid #e1e3e8;
        }

        .vehicle-name-link {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .vehicle-name {
            font-size: 13px;
            font-weight: 800;
            color: #1f2937;
            line-height: 1.3;
            transition: all 0.2s ease;
        }

        .vehicle-name-link:hover .vehicle-name {
            color: var(--color-blue);
            text-decoration: underline;
        }

        .plate-badge {
            background: #0f172a;
            color: #fff;
            border-radius: 7px;
            padding: 3px 8px;
            font-size: 11px;
            letter-spacing: .04em;
            font-weight: 800;
        }

        .station-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .station-badge-success {
            background: #ecfeff;
            color: #0f766e;
            border: 1px solid #bae6fd;
        }

        .station-badge-danger {
            background: #fee2e2 !important;
            color: #b91c1c !important;
            border: 1px solid #fecaca !important;
        }

        .ratio-pill {
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 900;
            border: 1px solid transparent;
        }

        .ratio-high {
            background: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        .ratio-medium {
            background: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }

        .ratio-low {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .availability-day-cell {
            padding: 6px 4px !important;
        }

        .status-dot {
            border: 0;
            width: 22px;
            height: 22px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #fff;
            cursor: pointer;
            transition: transform .12s ease, box-shadow .12s ease, opacity .12s ease;
        }

        .status-dot:hover {
            transform: scale(1.12);
            box-shadow: 0 4px 12px rgba(15, 23, 42, .18);
            opacity: .95;
        }

        .status-dot.is-ok {
            background: var(--color-ok);
        }

        .status-dot.is-partial {
            background: linear-gradient(90deg, var(--color-ok) 0%, var(--color-ok) 50%, var(--color-partial) 50%, var(--color-partial) 100%);
        }

        .status-dot.is-reserved {
            background: var(--color-reserved);
            color: #fff;
        }

        .status-dot.is-overdue {
            background: var(--color-overdue);
            color: #fff;
            animation: pulse-overdue 1.5s ease-in-out infinite;
        }

        .status-dot.is-off {
            background: var(--color-off);
        }

        .status-dot.is-pending {
            background: var(--color-pending);
        }

        @keyframes pulse-overdue {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .55;
            }
        }

        .availability-detail-panel {
            position: fixed;
            top: 90px;
            right: 24px;
            width: 400px;
            max-width: calc(100vw - 48px);
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
            border: 1px solid #e5e7eb;
            z-index: 1060;
            display: none;
            overflow: hidden;
        }

        .availability-detail-panel.is-open {
            display: block;
        }

        .availability-panel-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .08);
            z-index: 1050;
        }

        .availability-panel-backdrop.is-open {
            display: block;
        }

        .availability-detail-header {
            padding: 16px 18px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            background: #f8fafc;
        }

        .availability-detail-body {
            padding: 18px;
        }

        .vehicle-detail-card {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .vehicle-detail-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 20px;
            flex: 0 0 auto;
        }

        .day-status-box {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            border-radius: 14px;
            padding: 13px;
            margin-bottom: 16px;
        }

        .detail-percent-badge {
            width: 54px;
            height: 54px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: #166534;
            border: 4px solid #22c55e;
            font-weight: 900;
            font-size: 13px;
        }

        .timeline-bar {
            position: relative;
            height: 13px;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .timeline-segment {
            position: absolute;
            top: 0;
            height: 100%;
            min-width: 1px;
        }

        .timeline-segment.ok {
            background: var(--color-ok);
        }

        .timeline-segment.partial {
            background: var(--color-partial);
        }

        .timeline-segment.reserved {
            background: var(--color-reserved);
        }

        .timeline-segment.overdue {
            background: var(--color-overdue);
        }

        .timeline-segment.off {
            background: var(--color-off);
        }

        .timeline-segment.pending {
            background: var(--color-pending);
        }

        .timeline-labels {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #64748b;
            margin-bottom: 16px;
        }

        .schedule-list {
            border: 1px solid #e5e7eb;
            border-radius: 13px;
            overflow: hidden;
            background: #fff;
        }

        .schedule-item {
            display: grid;
            grid-template-columns: 14px 100px 1fr;
            gap: 8px;
            align-items: center;
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .schedule-item:last-child {
            border-bottom: 0;
        }

        .schedule-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
        }

        .schedule-dot.ok {
            background: var(--color-ok);
        }

        .schedule-dot.partial {
            background: var(--color-partial);
        }

        .schedule-dot.reserved {
            background: var(--color-reserved);
        }

        .schedule-dot.overdue {
            background: var(--color-overdue);
        }

        .schedule-dot.off {
            background: var(--color-off);
        }

        .schedule-dot.pending {
            background: var(--color-pending);
        }

        @media (max-width: 992px) {
            .availability-table-wrap {
                max-height: 68vh;
            }

            .col-group {
                left: 0;
                min-width: 160px;
                max-width: 160px;
            }

            .col-name {
                left: 160px;
                min-width: 110px;
                max-width: 110px;
            }

            .col-plate {
                left: 270px;
                min-width: 95px;
                max-width: 95px;
            }

            .col-station {
                display: none;
            }

            .col-ratio {
                left: 365px;
                min-width: 70px;
                max-width: 70px;
            }

            .availability-detail-panel {
                top: 70px;
                right: 12px;
                left: 12px;
                width: auto;
                max-width: none;
            }
        }

        .schedule-item-expanded {
            grid-template-columns: 14px 105px 1fr;
            align-items: flex-start;
        }

        .schedule-time {
            font-weight: 700;
            color: #334155;
            font-size: 12px;
        }

        .schedule-content {
            min-width: 0;
        }

        .schedule-title {
            font-weight: 800;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.4;
        }

        .schedule-details {
            margin-top: 7px;
            padding: 9px 10px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 11px;
            line-height: 1.6;
        }

        .schedule-details div+div {
            margin-top: 2px;
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            const rows = Array.from(document.querySelectorAll('#availabilityRows tr[data-search]'));
            const searchInput = document.getElementById('tableSearch');
            const statusButtons = Array.from(document.querySelectorAll('.js-status-filter'));
            const visibleRowsCount = document.getElementById('visibleRowsCount');

            const panel = document.getElementById('availabilityDetailPanel');
            const backdrop = document.getElementById('availabilityPanelBackdrop');
            const closeButton = document.getElementById('closeAvailabilityDetail');

            const detailTitle = document.getElementById('detailTitle');
            const detailSubtitle = document.getElementById('detailSubtitle');
            const detailVehicle = document.getElementById('detailVehicle');
            const detailVehicleMeta = document.getElementById('detailVehicleMeta');
            const detailStation = document.getElementById('detailStation');
            const detailStatus = document.getElementById('detailStatus');
            const detailPercent = document.getElementById('detailPercent');
            const detailTimelineBar = document.getElementById('detailTimelineBar');
            const detailScheduleList = document.getElementById('detailScheduleList');

            let activeStatus = 'all';

            function normalize(value) {
                return (value || '').toString().trim().toLowerCase();
            }

            function escapeHtml(value) {
                return (value || '').toString()
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function tipoLabel(tipo) {
                const labels = {
                    ok: 'Disponível',
                    partial: 'Parcial',
                    reserved: 'Reservado',
                    overdue: 'Reserva não finalizada',
                    off: 'Indisponível',
                    pending: 'Sem local'
                };

                return labels[tipo] || tipo;
            }

            function refreshRows() {
                const term = normalize(searchInput ? searchInput.value : '');
                let visible = 0;

                rows.forEach((row) => {
                    const searchText = normalize(row.getAttribute('data-search'));
                    const status = normalize(row.getAttribute('data-status'));

                    const bySearch = term === '' || searchText.includes(term);
                    const byStatus = activeStatus === 'all' || status === activeStatus;

                    const show = bySearch && byStatus;

                    row.style.display = show ? '' : 'none';

                    if (show) {
                        visible++;
                    }
                });

                if (visibleRowsCount) {
                    visibleRowsCount.textContent = visible.toString();
                }
            }

            function openPanel(button) {
                const veiculo = button.dataset.veiculo || '-';
                const placa = button.dataset.placa || '-';
                const modelo = button.dataset.modelo || '-';
                const estacao = button.dataset.estacao || '-';
                const dia = button.dataset.dia || '-';
                const titulo = button.dataset.titulo || '-';
                const tipo = button.dataset.tipo || 'off';
                const percentual = button.dataset.percentual || '0';

                let intervalos = [];

                try {
                    intervalos = JSON.parse(button.dataset.intervalos || '[]');
                } catch (error) {
                    intervalos = [];
                }

                detailTitle.textContent = 'Detalhes do dia — ' + dia;
                detailSubtitle.textContent = tipoLabel(tipo);
                detailVehicle.textContent = veiculo;
                detailVehicleMeta.textContent = modelo + ' · ' + placa;
                detailStation.textContent = estacao;
                detailStatus.textContent = titulo;
                detailPercent.textContent = percentual;

                detailTimelineBar.innerHTML = '';
                detailScheduleList.innerHTML = '';

                if (!intervalos.length) {
                    const fallbackTipo = tipo || 'off';
                    const fallbackLabel = titulo || tipoLabel(fallbackTipo);

                    detailTimelineBar.innerHTML = `
                        <span class="timeline-segment ${escapeHtml(fallbackTipo)}" style="left:0;width:100%;"></span>
                    `;

                    detailScheduleList.innerHTML = `
                        <div class="schedule-item schedule-item-expanded">
                            <span class="schedule-dot ${escapeHtml(fallbackTipo)}"></span>

                            <div class="schedule-time">
                                00:00 - 23:59
                            </div>

                            <div class="schedule-content">
                                <div class="schedule-title">${escapeHtml(fallbackLabel)}</div>
                            </div>
                        </div>
                    `;
                }

                intervalos.forEach((item) => {
                    const itemTipo = item.tipo || 'off';
                    const inicio = item.inicio || '--:--';
                    const fim = item.fim || '--:--';
                    const label = item.titulo || tipoLabel(itemTipo);
                    const left = Number(item.percentual_inicio || 0);
                    const width = Math.max(Number(item.percentual_largura || 0), 1);

                    const segment = document.createElement('span');

                    segment.className = 'timeline-segment ' + itemTipo;
                    segment.style.left = left + '%';
                    segment.style.width = width + '%';
                    segment.title = inicio + ' - ' + fim + ' | ' + label;

                    detailTimelineBar.appendChild(segment);

                    let detalhesHtml = '';

                    if (item.detalhes && item.detalhes.origem === 'movimentacao') {
                        detalhesHtml = `
                            <div class="schedule-details">
                                <div><strong>Movimentação:</strong> #${escapeHtml(item.detalhes.id || '-')}</div>
                                <div><strong>Tipo:</strong> ${escapeHtml(item.detalhes.tipo_movimentacao || '-')}</div>
                                <div><strong>Status:</strong> ${escapeHtml(item.detalhes.status_movimentacao || '-')}</div>
                                <div><strong>Origem:</strong> ${escapeHtml(item.detalhes.local_origem || '-')}</div>
                                <div><strong>Destino:</strong> ${escapeHtml(item.detalhes.local_destino || '-')}</div>
                                <div><strong>Registro:</strong> ${escapeHtml(item.detalhes.data_registro || '-')}</div>
                                <div><strong>Saída:</strong> ${escapeHtml(item.detalhes.data_saida || '-')}</div>
                                <div><strong>Chegada prevista:</strong> ${escapeHtml(item.detalhes.data_chegada_prevista || '-')}</div>
                                <div><strong>Chegada real:</strong> ${escapeHtml(item.detalhes.data_chegada_real || 'Não registrada')}</div>
                                ${
                                    item.detalhes.observacoes
                                        ? `<div><strong>Observações:</strong> ${escapeHtml(item.detalhes.observacoes)}</div>`
                                        : ''
                                }
                            </div>
                        `;
                    }

                    detailScheduleList.insertAdjacentHTML('beforeend', `
                        <div class="schedule-item schedule-item-expanded">
                            <span class="schedule-dot ${escapeHtml(itemTipo)}"></span>

                            <div class="schedule-time">
                                ${escapeHtml(inicio)} - ${escapeHtml(fim)}
                            </div>

                            <div class="schedule-content">
                                <div class="schedule-title">${escapeHtml(label)}</div>
                                ${detalhesHtml}
                            </div>
                        </div>
                    `);
                });

                panel.classList.add('is-open');
                backdrop.classList.add('is-open');
            }

            function closePanel() {
                panel.classList.remove('is-open');
                backdrop.classList.remove('is-open');
            }

            statusButtons.forEach((button) => {
                button.addEventListener('click', function() {
                    activeStatus = normalize(this.dataset.status || 'all');

                    statusButtons.forEach((item) => item.classList.remove('is-active'));

                    this.classList.add('is-active');

                    refreshRows();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', refreshRows);
            }

            document.querySelectorAll('.js-day-detail').forEach((button) => {
                button.addEventListener('click', function() {
                    openPanel(this);
                });
            });

            if (closeButton) {
                closeButton.addEventListener('click', closePanel);
            }

            if (backdrop) {
                backdrop.addEventListener('click', closePanel);
            }

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closePanel();
                }
            });

            refreshRows();
        });
    </script>
@stop
