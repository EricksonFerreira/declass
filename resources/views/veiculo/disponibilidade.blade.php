@extends('adminlte::page')

@section('title', $titulo ?? 'Disponibilidade de Veiculos')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo ?? 'Disponibilidade de Veiculos'" />
@stop

@section('content')
    <div class="availability-page">
        <div class="availability-hero card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form action="{{ route('veiculo.disponibilidade') }}" method="GET" class="row align-items-end">
                    <div class="col-lg-3 col-md-4 mb-2">
                        <label class="text-uppercase small font-weight-bold text-muted mb-1">Ano</label>
                        <select name="ano" class="form-control form-control-sm">
                            @foreach($anos as $ano)
                                <option value="{{ $ano }}" {{ (string) request('ano', date('Y')) === (string) $ano ? 'selected' : '' }}>
                                    {{ $ano }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4 mb-2">
                        <label class="text-uppercase small font-weight-bold text-muted mb-1">Mes</label>
                        <select name="mes" class="form-control form-control-sm">
                            @foreach($meses as $mes => $nomeMes)
                                <option value="{{ $mes }}" {{ (int) request('mes', date('n')) === (int) $mes ? 'selected' : '' }}>
                                    {{ $nomeMes }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4 mb-2">
                        <label class="text-uppercase small font-weight-bold text-muted mb-1">Buscar veiculo</label>
                        <input id="tableSearch" type="text" class="form-control form-control-sm" placeholder="Placa, modelo ou grupo...">
                    </div>
                    <div class="col-lg-3 mb-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm mr-2">
                            <i class="fas fa-filter mr-1"></i>Aplicar periodo
                        </button>
                        <a href="{{ route('veiculo.disponibilidade') }}" class="btn btn-outline-secondary btn-sm">
                            Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-2">
                <button type="button" class="kpi-card kpi-total is-active js-status-filter" data-status="all">
                    <div class="kpi-icon"><i class="fas fa-car-side"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Todos os veiculos</span>
                        <strong class="kpi-value">{{ $totalVeiculos }}</strong>
                    </div>
                </button>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <button type="button" class="kpi-card kpi-available js-status-filter" data-status="available">
                    <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Com disponibilidade</span>
                        <strong class="kpi-value">{{ $veiculosDisponiveis }}</strong>
                    </div>
                </button>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <button type="button" class="kpi-card kpi-reserved js-status-filter" data-status="reserved">
                    <div class="kpi-icon"><i class="fas fa-clock"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Reservados</span>
                        <strong class="kpi-value">{{ $veiculosReservados }}</strong>
                    </div>
                </button>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <button type="button" class="kpi-card kpi-unavailable js-status-filter" data-status="unavailable">
                    <div class="kpi-icon"><i class="fas fa-ban"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Indisponiveis</span>
                        <strong class="kpi-value">{{ $veiculosIndisponiveis }}</strong>
                    </div>
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold mb-2 mb-md-0">Grade diaria de disponibilidade</h3>
                    <div class="legend d-flex flex-wrap">
                        <span class="legend-item"><i class="fas fa-circle text-success"></i> Disponivel</span>
                        <span class="legend-item"><i class="fas fa-circle text-warning"></i> Indisponivel por reserva</span>
                        <span class="legend-item"><i class="fas fa-circle text-danger"></i> Indisponivel</span>
                        <span class="legend-item"><i class="fas fa-circle text-secondary"></i> Sem local (nao reservavel)</span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="availability-table-wrap">
                    <table class="table table-hover table-sm text-nowrap mb-0 availability-table">
                        <thead>
                            <tr>
                                <th class="sticky-column col-group">Grupo</th>
                                <th class="sticky-column col-model">Modelo</th>
                                <th class="sticky-column col-plate">Placa</th>
                                <th class="sticky-column col-ratio text-center" title="Percentual de disponibilidade no mes">Disp.</th>
                                @foreach($diasDoMes as $dia)
                                    <th class="text-center day-col">{{ str_pad((string) $dia, 2, '0', STR_PAD_LEFT) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="availabilityRows">
                            @forelse($veiculos as $veiculo)
                                @php
                                    $grupo = trim((string) ($veiculo->grupoFrota->nome ?? 'Sem grupo'));
                                    $modelo = trim((string) ($veiculo->modelo->nome ?? 'Sem modelo'));
                                    $placa = trim((string) ($veiculo->placa_licenca ?? 'Sem placa'));

                                    $availableCount = 0;
                                    $reservedCount = 0;
                                    foreach ($diasDoMes as $diaLoop) {
                                        $tipo = $grade[$veiculo->id][$diaLoop]['tipo'] ?? 'off';
                                        if ($tipo === 'ok') {
                                            $availableCount++;
                                        } elseif ($tipo === 'reserved') {
                                            $reservedCount++;
                                        }
                                    }

                                    $totalDias = max(count($diasDoMes), 1);
                                    $ratio = (int) round(($availableCount / $totalDias) * 100);
                                    $rowStatus = ($availableCount === $totalDias)
                                        ? 'available'
                                        : (($availableCount === 0 && $reservedCount === 0) ? 'unavailable' : 'reserved');
                                @endphp
                                <tr
                                    data-search="{{ strtolower($grupo . ' ' . $modelo . ' ' . $placa) }}"
                                    data-status="{{ $rowStatus }}"
                                >
                                    <td class="sticky-column col-group">{{ $grupo }}</td>
                                    <td class="sticky-column col-model">{{ $modelo }}</td>
                                    <td class="sticky-column col-plate">
                                        <span class="plate-badge">{{ $placa }}</span>
                                    </td>
                                    <td class="sticky-column col-ratio text-center">
                                        <span class="ratio-pill">{{ $ratio }}%</span>
                                    </td>
                                    @foreach($diasDoMes as $dia)
                                        @php
                                            $cell = $grade[$veiculo->id][$dia] ?? ['tipo' => 'off', 'titulo' => 'Indisponivel', 'reserva_id' => null];
                                        @endphp
                                        <td class="text-center">
                                            @if(($cell['tipo'] ?? null) === 'reserved' && !empty($cell['reserva_id']))
                                                <a
                                                    href="{{ route('reserva.show', $cell['reserva_id']) }}"
                                                    class="status-dot is-reserved"
                                                    style="text-decoration: none;"
                                                    title="{{ $cell['titulo'] ?? 'Indisponivel por reserva' }}"
                                                >
                                                    <i class="fas fa-calendar-check"></i>
                                                </a>
                                            @elseif(($cell['tipo'] ?? null) === 'pending')
                                                <span class="status-dot is-pending" title="{{ $cell['titulo'] ?? 'Sem local' }}"><i class="fas fa-map-marker-alt"></i></span>
                                            @elseif(($cell['tipo'] ?? null) === 'ok')
                                                <span class="status-dot is-ok" title="{{ $cell['titulo'] ?? 'Disponivel' }}"><i class="fas fa-check"></i></span>
                                            @else
                                                <span class="status-dot is-off" title="{{ $cell['titulo'] ?? 'Indisponivel' }}"><i class="fas fa-times"></i></span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 4 + count($diasDoMes) }}" class="text-center text-muted py-4">
                                        Nenhum veiculo encontrado para o periodo selecionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Arraste horizontalmente para ver todos os dias do mes.
                </small>
                <small class="text-muted">
                    <span id="visibleRowsCount">{{ $veiculos->count() }}</span> veiculo(s) em exibicao
                </small>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .availability-hero {
            background: linear-gradient(120deg, #f8fafc 0%, #eef2ff 100%);
        }

        .kpi-card {
            width: 100%;
            border: 0;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            color: #0f172a;
            box-shadow: 0 6px 20px rgba(15, 23, 42, .08);
            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
            text-align: left;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
        }

        .kpi-card .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .kpi-meta .kpi-label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .03em;
            opacity: .9;
        }

        .kpi-meta .kpi-value {
            font-size: 24px;
            line-height: 1.1;
        }

        .kpi-total { background: linear-gradient(135deg, #e2e8f0, #cbd5e1); }
        .kpi-total .kpi-icon { background: #94a3b8; color: #fff; }
        .kpi-available { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
        .kpi-available .kpi-icon { background: #16a34a; color: #fff; }
        .kpi-reserved { background: linear-gradient(135deg, #fef9c3, #fde68a); }
        .kpi-reserved .kpi-icon { background: #d97706; color: #fff; }
        .kpi-unavailable { background: linear-gradient(135deg, #fee2e2, #fecaca); }
        .kpi-unavailable .kpi-icon { background: #dc2626; color: #fff; }

        .kpi-card.is-active {
            outline: 2px solid rgba(15, 23, 42, .35);
            filter: saturate(1.08);
        }

        .legend {
            gap: 14px;
            font-size: 12px;
            color: #475569;
        }

        .legend-item i {
            font-size: 10px;
            margin-right: 4px;
        }

        .availability-table-wrap {
            max-height: 72vh;
            overflow: auto;
            position: relative;
            border-top: 1px solid #e2e8f0;
        }

        .availability-table thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
            font-size: 12px;
            text-transform: uppercase;
            color: #334155;
            letter-spacing: .02em;
        }

        .availability-table thead .sticky-column {
            z-index: 30;
            background: #f8fafc;
        }

        .availability-table tbody tr:hover td {
            background: #f8fafc;
        }

        .sticky-column {
            position: sticky;
            background: #fff;
            z-index: 10;
            box-shadow: 1px 0 0 #e2e8f0;
        }

        .col-group { left: 0; min-width: 160px; }
        .col-model { left: 160px; min-width: 160px; }
        .col-plate { left: 320px; min-width: 130px; }
        .col-ratio { left: 450px; min-width: 80px; }
        .day-col { min-width: 42px; }

        .plate-badge {
            background: #0f172a;
            color: #fff;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 11px;
            letter-spacing: .04em;
            font-weight: 700;
        }

        .ratio-pill {
            background: #e2e8f0;
            color: #0f172a;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-dot {
            width: 20px;
            height: 20px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #fff;
        }

        .status-dot.is-ok { background: #16a34a; }
        .status-dot.is-off { background: #dc2626; }
        .status-dot.is-reserved { background: #d97706; }
        .status-dot.is-pending { background: #64748b; }

        @media (max-width: 992px) {
            .availability-table-wrap {
                max-height: 68vh;
            }

            .col-group { left: 0; min-width: 130px; }
            .col-model { left: 130px; min-width: 130px; }
            .col-plate { left: 260px; min-width: 120px; }
            .col-ratio { left: 380px; min-width: 76px; }
        }
    </style>
@stop

@section('js')
    <script>
        $(function () {
            const rows = Array.from(document.querySelectorAll('#availabilityRows tr[data-search]'));
            const searchInput = document.getElementById('tableSearch');
            const statusButtons = Array.from(document.querySelectorAll('.js-status-filter'));
            const visibleRowsCount = document.getElementById('visibleRowsCount');
            let activeStatus = 'all';

            function normalize(value) {
                return (value || '').toString().trim().toLowerCase();
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

            statusButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    activeStatus = normalize(this.dataset.status || 'all');
                    statusButtons.forEach((item) => item.classList.remove('is-active'));
                    this.classList.add('is-active');
                    refreshRows();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', refreshRows);
            }

            refreshRows();
        });
    </script>
@stop
